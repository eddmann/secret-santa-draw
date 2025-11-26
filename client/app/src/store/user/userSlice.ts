import { createSlice } from '@reduxjs/toolkit';

import { bootstrap, login, register } from './actions';

export const initialState = {
  isBootstrapping: true,
  canLogin: false,
  canLogout: false,
  canRegister: false,
  canAccessGroups: false,
  canDeleteAccount: false,

  isLoggingIn: false,
  isRegistering: false,

  name: null as string | null,
  email: null as string | null,
};

export const userSlice = createSlice({
  name: 'user',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder.addCase(bootstrap.pending, (state) => {
      state.isBootstrapping = true;
    });
    builder.addCase(bootstrap.fulfilled, (state, action) => {
      state.isBootstrapping = false;
      state.canLogin = action.payload.canLogin;
      state.canLogout = action.payload.canLogout;
      state.canRegister = action.payload.canRegister;
      state.canAccessGroups = action.payload.canAccessGroups;
      state.canDeleteAccount = action.payload.canDeleteAccount;
      state.name = action.payload.name;
      state.email = action.payload.email;
    });
    builder.addCase(bootstrap.rejected, () => {
      return { ...initialState, isBootstrapping: false };
    });

    builder.addCase(login.pending, (state) => {
      state.isLoggingIn = true;
    });
    builder.addCase(login.fulfilled, (state) => {
      state.isLoggingIn = false;
    });
    builder.addCase(login.rejected, (state) => {
      state.isLoggingIn = false;
    });

    builder.addCase(register.pending, (state) => {
      state.isRegistering = true;
    });
    builder.addCase(register.fulfilled, (state) => {
      state.isRegistering = false;
    });
    builder.addCase(register.rejected, (state) => {
      state.isRegistering = false;
    });
  },
});
