import { createAsyncThunk } from '@reduxjs/toolkit';

import { client, notifyAndThrowErrorMessage } from '@/store/api';
import { fetchGroup } from '@/store/remoteGroups/actions';
import { RemoteEntry, RemoteGroup } from '@/types';

export const draw = createAsyncThunk(
  'remoteEntry/draw',
  async ({ id, description, participants, exclusions }: RemoteEntry & { id: RemoteGroup['id'] }, { dispatch }) => {
    try {
      const action = await client.go(atob(id)).follow('conduct-draw');
      await action.post({
        data: {
          description,
          participants: participants.map((participant) => ({
            name: participant.name,
            email: participant.email,
            exclusions: exclusions[participant.email] ?? [],
          })),
        },
      });

      client.clearResourceCache([atob(id)], []);
      await dispatch(fetchGroup({ id }));
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Failed to conduct draw.');
    }
  },
);
