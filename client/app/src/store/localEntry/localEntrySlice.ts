import type { PayloadAction } from '@reduxjs/toolkit';
import { createSlice } from '@reduxjs/toolkit';

import { LocalEntry, LocalParticipant } from '@/types';

export const initialState: LocalEntry = {
  title: '',
  participants: [],
  exclusions: {},
};

export const localEntrySlice = createSlice({
  name: 'localEntry',
  initialState,
  reducers: {
    startLocalDraw() {
      return initialState;
    },
    updateTitle(state, action: PayloadAction<string>) {
      state.title = action.payload;
    },
    addParticipant(state, action: PayloadAction<LocalParticipant>) {
      state.participants = [...state.participants, action.payload];
    },
    removeParticipant(state, action: PayloadAction<LocalParticipant>) {
      state.participants = state.participants.filter((participant) => participant !== action.payload);
      state.exclusions = Object.keys(state.exclusions).reduce(
        (exclusions, participant) =>
          participant === action.payload || !state.exclusions[participant]
            ? exclusions
            : {
                ...exclusions,
                [participant]: state.exclusions[participant].filter((excluded) => excluded !== participant),
              },
        {},
      );
    },
    toggleExclusion(state, action: PayloadAction<{ participant: LocalParticipant; exclusion: LocalParticipant }>) {
      const { participant, exclusion } = action.payload;
      const exclusions = state.exclusions[participant] ?? [];
      state.exclusions = {
        ...state.exclusions,
        [participant]: exclusions.includes(exclusion)
          ? exclusions.filter((excluded) => excluded !== exclusion)
          : [...exclusions, exclusion],
      };
    },
  },
});

export const { startLocalDraw, updateTitle, addParticipant, removeParticipant, toggleExclusion } =
  localEntrySlice.actions;
