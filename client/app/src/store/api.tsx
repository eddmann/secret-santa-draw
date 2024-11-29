import { Ketting } from 'ketting';
import { toast } from 'react-toastify';

import { BOOTSTRAP_URI } from '@/env';

export const client = new Ketting(BOOTSTRAP_URI);

client.use((request, next) => {
  const token = new URLSearchParams(window.location.search).get('token');
  if (token) request.headers.set('X-Access-Token', token);
  return next(request);
});

/* eslint-disable */
export const notifyAndThrowErrorMessage = async (error: any, defaultMessage = 'An error occurred.') => {
  const message: string =
    (error.response &&
      (await error.response
        .json()
        .then((data: any) => data?.message)
        .catch(() => null))) ||
    defaultMessage;

  toast.error(message);

  throw new Error(message);
};
/* eslint-enable */
