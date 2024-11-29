import { createSlice } from '@reduxjs/toolkit';

import { RemoteDetailedDraw } from '@/types';

import { fetchDraw } from './actions';

type State = {
  draw: RemoteDetailedDraw | null;
  isLoadingDraw: boolean;
};

export const initialState: State = {
  draw: null,
  isLoadingDraw: true,
};

export const remoteDrawsSlice = createSlice({
  name: 'remoteDraws',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder.addCase(fetchDraw.pending, (state) => {
      state.isLoadingDraw = true;
    });
    builder.addCase(fetchDraw.fulfilled, (state, action) => {
      state.draw = action.payload.draw;
      state.isLoadingDraw = false;
    });
    builder.addCase(fetchDraw.rejected, (state) => {
      state.isLoadingDraw = false;
    });
  },
});
