import { createAsyncThunk } from '@reduxjs/toolkit';
import { toast } from 'react-toastify';

import { client, notifyAndThrowErrorMessage } from '@/store/api';

export const bootstrap = createAsyncThunk('user/bootstrap', async () => {
  let resource;
  try {
    client.clearCache();
    resource = await client.go().get();
  } catch (error) {
    setTimeout(() => toast.error('Working in offline mode.'), 0);
    throw error;
  }

  const data = resource.data as { user?: { name: string; email: string } };

  return {
    canLogin: resource.links.has('login'),
    canRegister: resource.links.has('register'),
    canLogout: resource.links.has('logout'),
    canAccessGroups: resource.links.has('groups'),
    canDeleteAccount: resource.links.has('delete-account'),
    name: data.user?.name ?? null,
    email: data.user?.email ?? null,
  };
});

export const login = createAsyncThunk(
  'user/login',
  async ({ email, password }: { email: string; password: string }, { dispatch }) => {
    try {
      const action = await client.go().follow('login');
      await action.post({
        data: {
          email,
          password,
        },
      });

      await dispatch(bootstrap());
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Failed to login.');
    }
  },
);

export const logout = createAsyncThunk('user/logout', async (_, { dispatch }) => {
  try {
    const action = await client.go().follow('logout');
    await action.post({});

    await dispatch(bootstrap());
  } catch (error) {
    await notifyAndThrowErrorMessage(error, 'Failed to logout.');
  }
});

export const register = createAsyncThunk(
  'user/register',
  async ({ name, email, password }: { name: string; email: string; password: string }, { dispatch }) => {
    try {
      const action = await client.go().follow('register');
      await action.post({
        data: {
          name,
          email,
          password,
        },
      });

      await dispatch(bootstrap());
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Failed to register.');
    }
  },
);

export const deleteAccount = createAsyncThunk('user/deleteAccount', async (_, { dispatch }) => {
  try {
    const action = await client.go().follow('delete-account');
    await action.delete();

    await dispatch(bootstrap());
  } catch (error) {
    await notifyAndThrowErrorMessage(error, 'Failed to delete your account.');
  }
});
