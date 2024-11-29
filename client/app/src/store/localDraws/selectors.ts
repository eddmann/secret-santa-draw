import { RootState } from '@/store';

export const drawsSelector = (state: RootState) => state.localDraws.draws;

export const drawStatusSelector = (state: RootState) => state.localDraws.status;
