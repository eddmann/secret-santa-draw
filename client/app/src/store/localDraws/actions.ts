import { createAsyncThunk } from '@reduxjs/toolkit';

import { LocalEntry } from '@/types';

import { allocator } from './allocator';

export const draw = createAsyncThunk('localDraws/draw', async (entry: LocalEntry) => {
  await new Promise((resolve) => setTimeout(resolve, 1000));
  return {
    id: '' + new Date().getTime(),
    entry,
    allocation: allocator(entry),
    at: new Date().getTime(),
  };
});
