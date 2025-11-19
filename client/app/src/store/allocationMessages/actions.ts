import { createAsyncThunk } from '@reduxjs/toolkit';
import { State } from 'ketting';

import { client, notifyAndThrowErrorMessage } from '@/store/api';
import {
  AllocationMessage,
  AllocationMessageDirection,
  AllocationMessageResource,
  AllocationMessagesResource,
  RemoteAllocation,
} from '@/types';

export const fetchMessages = createAsyncThunk(
  'allocationMessages/fetch',
  async ({
    allocationId,
    direction,
  }: {
    allocationId: RemoteAllocation['id'];
    direction: AllocationMessageDirection;
  }) => {
    const allocationState = client.go(atob(allocationId));

    const messagesState: State<AllocationMessagesResource> = await (
      await allocationState.follow(direction === 'to-recipient' ? 'messages-to-recipient' : 'messages-from-santa')
    ).get();

    const messages: AllocationMessage[] = [];
    for (const messageResource of messagesState.followAll('messages')) {
      const state: State<AllocationMessageResource> = await messageResource.get();
      messages.push({
        id: btoa(state.uri),
        message: state.data.message,
        fromMe: state.data.is_from_me,
        createdAt: state.data.created_at,
      });
    }

    return {
      allocationId,
      direction,
      messages,
    };
  },
);

export const sendMessage = createAsyncThunk(
  'allocationMessages/send',
  async (
    {
      allocationId,
      direction,
      message,
    }: { allocationId: RemoteAllocation['id']; direction: AllocationMessageDirection; message: string },
    { dispatch },
  ) => {
    try {
      const allocationState = client.go(atob(allocationId));
      const messagesLink = direction === 'to-recipient' ? 'messages-to-recipient' : 'messages-from-santa';

      const messagesCollection = await allocationState.follow(messagesLink);
      await messagesCollection.get();

      const sendAction = await messagesCollection.follow('send-message');
      await sendAction.post({ data: { message } });

      client.clearResourceCache([messagesCollection.uri], []);
      await dispatch(fetchMessages({ allocationId, direction }));
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Failed to send message.');
    }
  },
);
