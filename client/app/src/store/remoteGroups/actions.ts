import { createAsyncThunk } from '@reduxjs/toolkit';
import { State } from 'ketting';

import { client, notifyAndThrowErrorMessage } from '@/store/api';
import { DrawResource, GroupResource, RemoteDraw, RemoteGroup } from '@/types';

export const fetchAllGroups = createAsyncThunk('remoteGroups/fetchAll', async () => {
  const groupsState = await (await client.follow('groups')).get();

  const groups: RemoteGroup[] = [];
  for (const groupResource of groupsState.followAll('groups')) {
    const state: State<GroupResource> = await groupResource.get();
    groups.push({ id: btoa(state.uri), title: state.data.title });
  }

  return {
    canAddGroup: groupsState.links.has('add-group'),
    groups,
  };
});

export const fetchGroup = createAsyncThunk('remoteGroups/fetch', async ({ id }: { id: RemoteGroup['id'] }) => {
  const groupState: State<GroupResource> = await client.go(atob(id)).get();

  const draws: RemoteDraw[] = [];
  for (const drawResource of await groupState.follow('draws').followAll('draws')) {
    const state: State<DrawResource> = await drawResource.get();
    draws.push({ id: btoa(state.uri), year: `${state.data.year}` });
  }

  return {
    group: {
      id: btoa(groupState.uri),
      title: groupState.data.title,
      canConductDraw: groupState.links.has('conduct-draw'),
      draws,
      previousYearsDrawPrefill: groupState.data.previous_years_draw_prefill,
    },
  };
});

export const addGroup = createAsyncThunk(
  'remoteGroups/add',
  async ({ title }: { title: RemoteGroup['title'] }, { dispatch }) => {
    try {
      const action = await client.go().follow('groups').follow('add-group');
      await action.post({ data: { title } });

      client.clearCache();
      await dispatch(fetchAllGroups());
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Failed to add group.');
    }
  },
);
