import { RootState } from '@/store';

export const isBootstrappingSelector = (state: RootState) => state.user.isBootstrapping;

export const userSelector = (state: RootState) => state.user;
