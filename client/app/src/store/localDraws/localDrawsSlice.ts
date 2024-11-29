import type { PayloadAction } from '@reduxjs/toolkit';
import { createSlice } from '@reduxjs/toolkit';

import { LocalDraw, LocalEntry } from '@/types';

import { draw } from './actions';

type State = {
  draws: LocalDraw[];
  status:
    | { type: 'idle' | 'drawing' }
    | { type: 'drawn'; id: LocalDraw['id'] }
    | { type: 'failed'; entry: LocalEntry; reason: string };
};

export const initialState: State = {
  draws: [],
  status: { type: 'idle' },
};

export const localDrawsSlice = createSlice({
  name: 'localDraws',
  initialState,
  reducers: {
    removeDraw(state, action: PayloadAction<LocalDraw['id']>) {
      state.draws = state.draws.filter((draw) => draw.id !== action.payload);
    },
    clearDrawStatus(state) {
      state.status = { type: 'idle' };
    },
  },
  extraReducers: (builder) => {
    builder.addCase(draw.pending, (state) => {
      state.status = { type: 'drawing' };
    });
    builder.addCase(draw.fulfilled, (state, action) => {
      state.status = { type: 'drawn', id: action.payload.id };
      state.draws.push(action.payload);
    });
    builder.addCase(draw.rejected, (state, action) => {
      state.status = {
        type: 'failed',
        entry: action.meta.arg,
        reason: action.error.message ?? '',
      };
    });
  },
});

export const { removeDraw, clearDrawStatus } = localDrawsSlice.actions;
