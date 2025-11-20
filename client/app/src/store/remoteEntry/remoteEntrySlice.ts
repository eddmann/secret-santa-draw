import type { PayloadAction } from '@reduxjs/toolkit';
import { createSlice } from '@reduxjs/toolkit';

import { RemoteDrawPrefill, RemoteEntry, RemoteParticipant } from '@/types';

import { draw } from './actions';

type State = RemoteEntry & {
  isDrawing: boolean;
  wasPrefilled: boolean;
};

export const initialState: State = {
  description: '',
  participants: [],
  exclusions: {},
  isDrawing: false,
  wasPrefilled: false,
};

export const remoteEntrySlice = createSlice({
  name: 'remoteEntry',
  initialState,
  reducers: {
    startDraw(
      _state,
      action: PayloadAction<
        | {
            prefill: RemoteDrawPrefill | null;
          }
        | undefined
      >,
    ) {
      if (action.payload?.prefill) {
        return {
          ...initialState,
          participants: action.payload.prefill.participants,
          exclusions: action.payload.prefill.exclusions,
          wasPrefilled: true,
        };
      }
      return initialState;
    },
    updateDescription(state, action: PayloadAction<string>) {
      state.description = action.payload;
    },
    addParticipant(state, action: PayloadAction<RemoteParticipant>) {
      state.participants = [...state.participants, action.payload];
    },
    removeParticipant(state, action: PayloadAction<RemoteParticipant['email']>) {
      state.participants = state.participants.filter((participant) => participant.email !== action.payload);
      state.exclusions = Object.keys(state.exclusions).reduce(
        (exclusions, participantEmail) =>
          participantEmail === action.payload || !state.exclusions[participantEmail]
            ? exclusions
            : {
                ...exclusions,
                [participantEmail]: state.exclusions[participantEmail].filter(
                  (excludedEmail) => excludedEmail !== participantEmail,
                ),
              },
        {},
      );
    },
    toggleExclusion(
      state,
      action: PayloadAction<{
        participantEmail: RemoteParticipant['email'];
        exclusionEmail: RemoteParticipant['email'];
      }>,
    ) {
      const { participantEmail, exclusionEmail } = action.payload;
      const exclusions = state.exclusions[participantEmail] ?? [];
      state.exclusions = {
        ...state.exclusions,
        [participantEmail]: exclusions.includes(exclusionEmail)
          ? exclusions.filter((excluded) => excluded !== exclusionEmail)
          : [...exclusions, exclusionEmail],
      };
    },
  },
  extraReducers: (builder) => {
    builder.addCase(draw.pending, (state) => {
      state.isDrawing = true;
    });
    builder.addCase(draw.fulfilled, () => {
      return initialState;
    });
    builder.addCase(draw.rejected, (state) => {
      state.isDrawing = false;
    });
  },
});

export const { startDraw, updateDescription, addParticipant, removeParticipant, toggleExclusion } =
  remoteEntrySlice.actions;
