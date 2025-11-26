import { unwrapResult } from '@reduxjs/toolkit';
import { useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import styled from 'styled-components';

import HomeIcon from '@/assets/home.svg?react';
import { Button } from '@/components/Button';
import { ConfirmDialog } from '@/components/ConfirmDialog';
import { Content } from '@/components/Content';
import { Header } from '@/components/Header';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { userSelector } from '@/store/user';
import { deleteAccount, logout } from '@/store/user/actions';

const ProfileCard = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: ${({ theme }) => theme.spacing.padding.l};
  padding: ${({ theme }) => theme.spacing.padding.l};
  background: rgb(170 4 37 / 60%);
  border: 2px solid #000;
  border-radius: 0.5rem;
  box-shadow: 3px 3px 0 0 rgb(0 0 0 / 20%);
`;

const Avatar = styled.div`
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: ${({ theme }) => theme.colors.text};
  color: ${({ theme }) => theme.colors.background};
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: ${({ theme }) => theme.typography.weight.extrabold};
`;

const ProfileName = styled.div`
  font-size: 1.75rem;
  font-weight: ${({ theme }) => theme.typography.weight.extrabold};
  text-align: center;
  line-height: 1.2;
`;

const ProfileEmail = styled.div`
  font-size: ${({ theme }) => theme.typography.size.m};
  opacity: 0.85;
  text-align: center;
  word-break: break-word;
`;

const ActionSection = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${({ theme }) => theme.spacing.padding.m};
`;

const SectionTitle = styled.h3`
  margin: 0 0 ${({ theme }) => theme.spacing.padding.s} 0;
  font-size: ${({ theme }) => theme.typography.size.s};
  opacity: 0.7;
  text-transform: uppercase;
  letter-spacing: 0.1em;
`;

export const Settings = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const user = useAppSelector(userSelector);
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

  if (!user.canLogout) return <Navigate to="/" replace />;

  return (
    <>
      <Header
        title="Settings"
        icon={<HomeIcon />}
        onClick={() => {
          navigate('/');
        }}
      />
      <Content>
        <ProfileCard>
          <Avatar>{user.name?.charAt(0).toUpperCase()}</Avatar>
          <ProfileName>{user.name}</ProfileName>
          <ProfileEmail>{user.email}</ProfileEmail>
        </ProfileCard>

        <ActionSection>
          <SectionTitle>Account</SectionTitle>
          <Button
            title="Logout"
            variant="large"
            onClick={() => {
              void dispatch(logout())
                .then(unwrapResult)
                .then(() => {
                  toast.success('Successfully logged out.');
                  navigate('/', { replace: true });
                });
            }}
          />

          {user.canDeleteAccount && (
            <Button
              title="Delete Account"
              variant="large"
              onClick={() => {
                setShowDeleteConfirm(true);
              }}
            />
          )}
        </ActionSection>
      </Content>

      <ConfirmDialog
        isOpen={showDeleteConfirm}
        title="Delete Account?"
        message="This action cannot be undone. All your data will be permanently deleted."
        confirmText="Delete"
        cancelText="Cancel"
        onCancel={() => {
          setShowDeleteConfirm(false);
        }}
        onConfirm={() => {
          void dispatch(deleteAccount())
            .then(unwrapResult)
            .then(() => {
              toast.success('Successfully deleted your account.');
              navigate('/', { replace: true });
            });
          setShowDeleteConfirm(false);
        }}
      />
    </>
  );
};
