import { unwrapResult } from '@reduxjs/toolkit';
import { useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';

import { BackIcon } from '@/components/BackIcon';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { Header } from '@/components/Header';
import InputField from '@/components/InputField';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { userSelector } from '@/store/user';
import { register } from '@/store/user/actions';

export const Register = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const user = useAppSelector(userSelector);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  if (!user.canRegister) return <Navigate to="/" replace />;

  return (
    <>
      <Header
        title="Register"
        icon={<BackIcon />}
        onClick={() => {
          navigate('/login');
        }}
      />
      <Content>
        <Description>Register an account to conduct a Secret Santa draw.</Description>

        <InputField
          value={name}
          onChange={(event) => {
            setName(event.target.value);
          }}
          placeholder="Name"
        />

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
          title={user.isRegistering ? 'Registering' : 'Register'}
          variant="large"
          onClick={() => {
            void dispatch(register({ name, email, password }))
              .then(unwrapResult)
              .then(() => {
                toast.success('You have succcessfully registered.');
                navigate('/', { replace: true });
              });
          }}
        />
      </Content>
    </>
  );
};
