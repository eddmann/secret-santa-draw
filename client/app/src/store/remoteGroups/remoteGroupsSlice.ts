import { createSlice } from '@reduxjs/toolkit';

import { RemoteDetailsGroup, RemoteGroup } from '@/types';

import { addGroup, fetchAllGroups, fetchGroup } from './actions';

type State = {
  canAddGroup: boolean;
  isAddingGroup: boolean;
  groups: RemoteGroup[];
  isLoadingGroups: boolean;
  group: RemoteDetailsGroup | null;
  isLoadingGroup: boolean;
};

export const initialState: State = {
  canAddGroup: false,
  isAddingGroup: false,
  groups: [],
  isLoadingGroups: true,
  group: null,
  isLoadingGroup: true,
};

export const remoteGroupsSlice = createSlice({
  name: 'remoteGroups',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder.addCase(fetchAllGroups.pending, (state) => {
      state.canAddGroup = false;
      state.isLoadingGroups = true;
    });
    builder.addCase(fetchAllGroups.fulfilled, (state, action) => {
      state.canAddGroup = action.payload.canAddGroup;
      state.isLoadingGroups = false;
      state.groups = action.payload.groups;
    });
    builder.addCase(fetchAllGroups.rejected, (state) => {
      state.canAddGroup = false;
      state.isLoadingGroups = false;
    });

    builder.addCase(addGroup.pending, (state) => {
      state.isAddingGroup = true;
    });
    builder.addCase(addGroup.fulfilled, (state) => {
      state.isAddingGroup = false;
    });
    builder.addCase(addGroup.rejected, (state) => {
      state.isAddingGroup = false;
    });

    builder.addCase(fetchGroup.pending, (state) => {
      state.isLoadingGroup = true;
    });
    builder.addCase(fetchGroup.fulfilled, (state, action) => {
      state.group = action.payload.group;
      state.isLoadingGroup = false;
    });
    builder.addCase(fetchGroup.rejected, (state) => {
      state.isLoadingGroup = false;
    });
  },
});
