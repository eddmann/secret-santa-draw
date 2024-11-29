import { useEffect } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';

import HomeIcon from '@/assets/home.svg?react';
import ShareIcon from '@/assets/share.svg?react';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { Header } from '@/components/Header';
import { List } from '@/components/List';
import { Loading } from '@/components/Loading';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { remoteGroupsSelector } from '@/store/remoteGroups';
import { fetchAllGroups } from '@/store/remoteGroups/actions';
import { userSelector } from '@/store/user';

export const ListRemoteGroups = () => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const user = useAppSelector(userSelector);
  const { groups, isLoadingGroups, canAddGroup } = useAppSelector(remoteGroupsSelector);

  useEffect(() => {
    if (!user.canAccessGroups) return;
    void dispatch(fetchAllGroups());
  }, [dispatch, user.canAccessGroups]);

  if (!user.canAccessGroups) return <Navigate to="/" replace />;

  if (isLoadingGroups) {
    return <Loading />;
  }

  return (
    <>
      <Header
        title="Remote"
        icon={<HomeIcon />}
        onClick={() => {
          navigate('/');
        }}
      />
      <Content>
        <Description>
          {groups.length > 0
            ? `Groups that you own or are allocated to are found below:`
            : `No draw group has been added yet, lets change that...`}
        </Description>

        {groups.length > 0 && (
          <List>
            {groups.map((group) => (
              <List.Item key={group.id}>
                <List.Title to={`/remote/${group.id}`}>{group.title}</List.Title>
                <Button
                  icon={<ShareIcon />}
                  onClick={(e) => {
                    e.preventDefault();
                    window.location.href = `/remote/${group.id}`;
                  }}
                />
              </List.Item>
            ))}
          </List>
        )}

        {canAddGroup && (
          <Button
            title="Add Group"
            variant="large"
            onClick={() => {
              navigate('/remote/add');
            }}
          />
        )}
      </Content>
    </>
  );
};
