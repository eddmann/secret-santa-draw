import { unwrapResult } from '@reduxjs/toolkit';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';

import { BackIcon } from '@/components/BackIcon';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { Header } from '@/components/Header';
import InputField from '@/components/InputField';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { remoteGroupsSelector } from '@/store/remoteGroups';
import { addGroup } from '@/store/remoteGroups/actions';

export const AddRemoteGroup = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const [title, setTitle] = useState('');

  const group = useAppSelector(remoteGroupsSelector);

  return (
    <>
      <Header
        title="Add Group"
        icon={<BackIcon />}
        onClick={() => {
          navigate('/remote');
        }}
      />
      <Content>
        <Description>Add a new draw group to conduct a Secret Santa draw.</Description>

        <InputField
          value={title}
          onChange={(event) => {
            setTitle(event.target.value);
          }}
          placeholder="Title"
        />

        <Button
          title={group.isAddingGroup ? 'Adding' : 'Add'}
          variant="large"
          onClick={() => {
            void dispatch(addGroup({ title }))
              .then(unwrapResult)
              .then(() => {
                toast.success('Successfully added group.');
                navigate('/remote');
              });
          }}
        />
      </Content>
    </>
  );
};
