import 'react-toastify/dist/ReactToastify.css';

import React from 'react';
import ReactDOM from 'react-dom/client';
import { Provider } from 'react-redux';
import { createBrowserRouter, redirect, RouterProvider } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import { ThemeProvider } from 'styled-components';
import { registerSW } from 'virtual:pwa-register';

import { BootstrapLoader } from './BootstrapLoader';
import GlobalStyles from './components/GlobalStyles';
import { Layout } from './components/Layout';
import { Home } from './routes/home';
import { ConductLocalDrawExclusions } from './routes/local/conduct-draw-exclusions';
import { ConductLocalDrawParticipants } from './routes/local/conduct-draw-participants';
import { ListLocalDraws } from './routes/local/list-draws';
import { ShowLocalDraw } from './routes/local/show-draw';
import { Login } from './routes/login';
import { Register } from './routes/register';
import { AddRemoteGroup } from './routes/remote/add-group';
import { ConductRemoteDrawExclusions } from './routes/remote/conduct-draw-exclusions';
import { ConductRemoteDrawParticipants } from './routes/remote/conduct-draw-participants';
import { ListRemoteGroups } from './routes/remote/list-groups';
import { MessageConversation } from './routes/remote/message-conversation';
import { ShowRemoteDraw } from './routes/remote/show-draw';
import { ShowRemoteGroup } from './routes/remote/show-group';
import { Settings } from './routes/settings';
import { store } from './store';
import { bootstrap } from './store/user/actions';
import { theme } from './theme';

registerSW({ immediate: true });

const router = createBrowserRouter([
  {
    path: '/',
    element: <Layout />,
    children: [
      {
        path: '',
        element: <Home />,
      },
      {
        path: 'login',
        element: <Login />,
      },
      {
        path: 'register',
        element: <Register />,
      },
      {
        path: 'settings',
        element: <Settings />,
      },
      {
        path: 'remote',
        element: <ListRemoteGroups />,
      },
      {
        path: 'remote/:id',
        element: <ShowRemoteGroup />,
      },
      {
        path: 'remote/add',
        element: <AddRemoteGroup />,
      },
      {
        path: 'remote/:id/draws/conduct/participants',
        element: <ConductRemoteDrawParticipants />,
      },
      {
        path: 'remote/:id/draws/conduct/exclusions',
        element: <ConductRemoteDrawExclusions />,
      },
      {
        path: 'remote/draws/:id',
        element: <ShowRemoteDraw />,
      },
      {
        path: 'remote/draws/:id/messages/to-recipient',
        element: <MessageConversation direction="to-recipient" />,
      },
      {
        path: 'remote/draws/:id/messages/from-santa',
        element: <MessageConversation direction="from-santa" />,
      },
      {
        path: 'local',
        element: <ListLocalDraws />,
      },
      {
        path: 'local/draws/conduct/participants',
        element: <ConductLocalDrawParticipants />,
      },
      {
        path: 'local/draws/conduct/exclusions',
        element: <ConductLocalDrawExclusions />,
      },
      {
        path: 'local/draws/:id',
        loader: ({ params: { id } }) => {
          const draw = store.getState().localDraws.draws.find((draw) => draw.id === id);
          return draw ?? redirect('/');
        },
        element: <ShowLocalDraw />,
      },
    ],
  },
]);

void store.dispatch(bootstrap());

// eslint-disable-next-line @typescript-eslint/no-non-null-assertion
ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ThemeProvider theme={theme}>
      <Provider store={store}>
        <GlobalStyles />
        <BootstrapLoader>
          <RouterProvider router={router} />
        </BootstrapLoader>
      </Provider>
    </ThemeProvider>
    <ToastContainer autoClose={2000} position="top-center" />
  </React.StrictMode>,
);
