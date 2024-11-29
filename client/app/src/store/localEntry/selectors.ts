import { RootState } from '@/store';

export const entrySelector = (state: RootState) => state.localEntry;

export const titleSelector = (state: RootState) => state.localEntry.title;

export const paripicantsSelector = (state: RootState) => state.localEntry.participants;

export const exclusionsSelector = (state: RootState) => state.localEntry.exclusions;
