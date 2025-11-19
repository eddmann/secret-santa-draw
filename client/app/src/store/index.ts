import { configureStore } from '@reduxjs/toolkit';

import { LocalDraw } from '@/types';

import { allocationMessagesSlice } from './allocationMessages';
import { localDrawsSlice } from './localDraws';
import { localEntrySlice } from './localEntry';
import { remoteDrawsSlice } from './remoteDraws';
import { remoteEntrySlice } from './remoteEntry';
import { remoteGroupsSlice } from './remoteGroups';
import { userSlice } from './user';

const LOCAL_STORAGE_DRAW_KEY = 'draws';

let preloadedState = undefined;
try {
  const localDraws = localStorage.getItem(LOCAL_STORAGE_DRAW_KEY);
  preloadedState = {
    localDraws: {
      ...localDrawsSlice.getInitialState(),
      draws: localDraws ? (JSON.parse(localDraws) as LocalDraw[]) : localDrawsSlice.getInitialState().draws,
    },
  };
} catch {
  // ignored
}

export const store = configureStore({
  reducer: {
    user: userSlice.reducer,
    localEntry: localEntrySlice.reducer,
    localDraws: localDrawsSlice.reducer,
    remoteGroups: remoteGroupsSlice.reducer,
    remoteEntry: remoteEntrySlice.reducer,
    remoteDraws: remoteDrawsSlice.reducer,
    allocationMessages: allocationMessagesSlice.reducer,
  },
  preloadedState,
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

store.subscribe(() => {
  try {
    localStorage.setItem(LOCAL_STORAGE_DRAW_KEY, JSON.stringify(store.getState().localDraws.draws));
  } catch {
    // ignored
  }
});
