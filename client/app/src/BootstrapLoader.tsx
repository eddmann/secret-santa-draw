import { PropsWithChildren } from 'react';

import { Loading } from '@/components/Loading';
import { useAppSelector } from '@/store/hooks';
import { isBootstrappingSelector } from '@/store/user';

export const BootstrapLoader = ({ children }: PropsWithChildren) => {
  const isBootstrapping = useAppSelector(isBootstrappingSelector);
  return isBootstrapping ? <Loading /> : children;
};
