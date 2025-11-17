import { createAsyncThunk } from '@reduxjs/toolkit';
import { State } from 'ketting';

import { client, notifyAndThrowErrorMessage } from '@/store/api';
import { fetchGroup } from '@/store/remoteGroups/actions';
import { AllocationResource, DrawResource, RemoteAllocation, RemoteDraw, RemoteGroup } from '@/types';

export const fetchDraw = createAsyncThunk('remoteDraws/fetch', async ({ id }: { id: RemoteDraw['id'] }) => {
  const drawState: State<DrawResource> = await client.go(atob(id)).get();

  const allocations: RemoteAllocation[] = [];
  if (drawState.links.has('allocations')) {
    for (const allocationResource of await drawState.follow('allocations').followAll('allocations')) {
      const state: State<AllocationResource> = await allocationResource.get();
      allocations.push({
        id: btoa(state.uri),
        from: state.data.from.name,
        to: state.data.to.name,
        fromIdeas: state.data.from.ideas,
        toIdeas: state.data.to.ideas,
        canProvideIdeas: state.links.has('provide-ideas'),
        token: state.data.from.access_token,
      });
    }
  }

  let allocation: RemoteAllocation | null = null;
  if (drawState.links.has('allocation')) {
    const state: State<AllocationResource> = await drawState.follow('allocation').get();
    allocation = {
      id: btoa(state.uri),
      from: state.data.from.name,
      to: state.data.to.name,
      fromIdeas: state.data.from.ideas,
      toIdeas: state.data.to.ideas,
      canProvideIdeas: state.links.has('provide-ideas'),
      token: state.data.from.access_token,
    };
  }

  return {
    draw: {
      id: btoa(drawState.uri),
      groupId: btoa(drawState.links.get('group')?.href ?? ''),
      title: drawState.data.title,
      year: `${drawState.data.year}`,
      description: drawState.data.description,
      allocations,
      allocation,
    },
  };
});

export const removeDraw = createAsyncThunk(
  'remoteDraws/remove',
  async ({ groupId, drawId }: { groupId: RemoteGroup['id']; drawId: RemoteDraw['id'] }, { dispatch }) => {
    try {
      const action = await client.go(atob(drawId)).follow('remove-draw');
      await action.delete();

      client.clearResourceCache([atob(groupId)], []);
      await dispatch(fetchGroup({ id: groupId }));
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Failed to remove draw.');
    }
  },
);

export const provideIdeas = createAsyncThunk(
  'remoteDraws/provideIdeas',
  async ({ id, ideas }: { id: RemoteDraw['id']; ideas: string[] }, { dispatch }) => {
    try {
      const action = await client.go(atob(id)).follow('allocation').follow('provide-ideas');
      await action.put({ data: { ideas } });

      // Clear the cache for the draw and allocation to ensure fresh data
      client.clearResourceCache([atob(id)], []);

      await dispatch(fetchDraw({ id }));
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Failed to provide ideas.');
    }
  },
);
