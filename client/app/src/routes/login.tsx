import { unwrapResult } from '@reduxjs/toolkit';
import { useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';

import HomeIcon from '@/assets/home.svg?react';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { Header } from '@/components/Header';
import InputField from '@/components/InputField';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { userSelector } from '@/store/user';
import { login } from '@/store/user/actions';

export const Login = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const user = useAppSelector(userSelector);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  if (!user.canLogin) return <Navigate to="/" replace />;

  return (
    <>
      <Header
        title="Login"
        icon={<HomeIcon />}
        onClick={() => {
          navigate('/');
        }}
      />
      <Content>
        <Description>Login to access your Secret Santa draws.</Description>

        <InputField
          value={email}
          onChange={(event) => {
            setEmail(event.target.value);
          }}
          placeholder="Email"
        />

        <InputField
          type="password"
          value={password}
          onChange={(event) => {
            setPassword(event.target.value);
          }}
          placeholder="Password"
        />

        <Button
          title={user.isLoggingIn ? 'Logging in' : 'Login'}
          variant="large"
          onClick={() => {
            void dispatch(login({ email, password }))
              .then(unwrapResult)
              .then(() => {
                toast.success('Successfully logged in.');
                navigate('/', { replace: true });
              });
          }}
        />

        <Description>Don&apos;t have an account? register to conduct a Secret Santa draw.</Description>

        <Button
          title="Register"
          variant="large"
          onClick={() => {
            navigate(`/register`);
          }}
        />
      </Content>
    </>
  );
};
