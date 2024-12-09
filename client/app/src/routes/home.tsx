import { unwrapResult } from '@reduxjs/toolkit';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import styled, { keyframes } from 'styled-components';

import { Button } from '@/components/Button';
import { SantaPopup } from '@/components/SantaPopup';
import { useSnowfall } from '@/hooks/useSnowfall';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { userSelector } from '@/store/user';
import { deleteAccount, logout } from '@/store/user/actions';

const slideDown = keyframes`
  from {
    transform: translateY(-200px);
  }
  to {
    transform: translateY(0px);
  }
`;

const Title = styled.h1`
  margin: 2rem auto 1rem;
  height: 20vh;
  max-height: 250px;

  img {
    height: 100%;
  }
`;

const NavigationRoot = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${({ theme }) => theme.spacing.padding.l};
  animation: ${slideDown} 1s ease;
`;

const DrawRoot = styled.div`
  display: flex;
  flex-direction: row;
  gap: ${({ theme }) => theme.spacing.padding.m};
`;

const DeleteAccount = styled.div`
  font-size: 0.85rem;
  position: fixed;
  cursor: pointer;
  z-index: 1000;
  bottom: calc(1rem + env(safe-area-inset-bottom));
  left: 10px;
`;

export const Home = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const user = useAppSelector(userSelector);
  const [deleteAccountConfirm, setDeleteAccountConfirm] = useState(false);

  const Snowfall = useSnowfall({
    snowflakes: {
      start: 200,
      max: 500,
    },
    speed: {
      start: [1.0, 3.0],
      max: [4.0, 10.0],
    },
    wind: {
      start: [-1, 1],
      max: [4.0, 7.0],
    },
  });

  return (
    <>
      <NavigationRoot>
        <Title>
          <img src="title.png" />
        </Title>
        <DrawRoot>
          <Button
            title="Local"
            variant="large"
            onClick={() => {
              navigate(`/local`);
            }}
          />
          <Button
            title="Remote"
            disabled={!user.canAccessGroups}
            variant="large"
            onClick={() => {
              navigate(`/remote`);
            }}
          />
        </DrawRoot>

        {user.canLogout ? (
          <Button
            title="Logout"
            variant="large"
            onClick={() => {
              void dispatch(logout())
                .then(unwrapResult)
                .then(() => {
                  toast.success('Successfully logged out.');
                });
            }}
          />
        ) : (
          <Button
            title="Login"
            disabled={!user.canLogin}
            variant="large"
            onClick={() => {
              navigate(`/login`);
            }}
          />
        )}
      </NavigationRoot>
      {user.canDeleteAccount && (
        <DeleteAccount
          onClick={() => {
            if (!deleteAccountConfirm) {
              setDeleteAccountConfirm(true);
              return;
            }

            void dispatch(deleteAccount())
              .then(unwrapResult)
              .then(() => {
                toast.success('Successfully deleted your account.');
              });
          }}
        >
          {deleteAccountConfirm ? 'Click to confirm?' : 'Delete Account'}
        </DeleteAccount>
      )}
      <SantaPopup />
      {Snowfall}
    </>
  );
};
