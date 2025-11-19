import { createSlice } from '@reduxjs/toolkit';

import { AllocationMessage, AllocationMessageDirection } from '@/types';

import { fetchMessages, sendMessage } from './actions';

type MessagesState = {
  messages: AllocationMessage[];
  isLoading: boolean;
  isSending: boolean;
};

type State = {
  conversations: Record<string, MessagesState | undefined>;
};

export const initialState: State = {
  conversations: {},
};

const getConversationKey = (allocationId: string, direction: AllocationMessageDirection) =>
  `${allocationId}:${direction}`;

export const allocationMessagesSlice = createSlice({
  name: 'allocationMessages',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder.addCase(fetchMessages.pending, (state, action) => {
      const key = getConversationKey(action.meta.arg.allocationId, action.meta.arg.direction);
      const conversation = state.conversations[key];
      if (!conversation) {
        state.conversations[key] = { messages: [], isLoading: true, isSending: false };
      } else if (conversation.messages.length === 0) {
        conversation.isLoading = true;
      }
    });
    builder.addCase(fetchMessages.fulfilled, (state, action) => {
      const key = getConversationKey(action.payload.allocationId, action.payload.direction);
      state.conversations[key] = {
        messages: action.payload.messages,
        isLoading: false,
        isSending: false,
      };
    });
    builder.addCase(fetchMessages.rejected, (state, action) => {
      const key = getConversationKey(action.meta.arg.allocationId, action.meta.arg.direction);
      const conversation = state.conversations[key];
      if (conversation) {
        conversation.isLoading = false;
      }
    });
    builder.addCase(sendMessage.pending, (state, action) => {
      const key = getConversationKey(action.meta.arg.allocationId, action.meta.arg.direction);
      const conversation = state.conversations[key];

      if (!conversation) {
        return;
      }

      conversation.isSending = true;
      conversation.messages.unshift({
        id: `temp-${Date.now()}`,
        message: action.meta.arg.message,
        fromMe: true,
        createdAt: new Date().toISOString(),
      });
    });
    builder.addCase(sendMessage.fulfilled, (state, action) => {
      const key = getConversationKey(action.meta.arg.allocationId, action.meta.arg.direction);
      const conversation = state.conversations[key];
      if (conversation) {
        conversation.isSending = false;
      }
    });
    builder.addCase(sendMessage.rejected, (state, action) => {
      const key = getConversationKey(action.meta.arg.allocationId, action.meta.arg.direction);
      const conversation = state.conversations[key];
      if (conversation) {
        conversation.isSending = false;
      }
    });
  },
});
