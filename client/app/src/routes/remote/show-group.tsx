import { unwrapResult } from '@reduxjs/toolkit';
import { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';

import TrashIcon from '@/assets/trash.svg?react';
import { BackIcon } from '@/components/BackIcon';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { Header } from '@/components/Header';
import { List } from '@/components/List';
import { Loading } from '@/components/Loading';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { removeDraw } from '@/store/remoteDraws/actions';
import { startDraw } from '@/store/remoteEntry';
import { remoteGroupsSelector } from '@/store/remoteGroups';
import { fetchGroup } from '@/store/remoteGroups/actions';

export const ShowRemoteGroup = () => {
  const { id = '' } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { group, isLoadingGroup } = useAppSelector(remoteGroupsSelector);

  useEffect(() => {
    void dispatch(fetchGroup({ id }));
  }, [id, dispatch]);

  if (isLoadingGroup) {
    return <Loading />;
  }

  if (!group) return null;

  return (
    <>
      <Header
        title={group.title}
        icon={<BackIcon />}
        onClick={() => {
          navigate('/remote');
        }}
      />
      <Content>
        <Description>
          {group.draws.length > 0
            ? `Previous draws which have taken place are found below:`
            : `No draws have taken place yet, lets change that...`}
        </Description>

        {group.draws.length > 0 && (
          <List>
            {group.draws.map((draw) => (
              <List.Item key={draw.id}>
                <List.Title to={`/remote/draws/${draw.id}`}>{draw.year}</List.Title>
                <Button
                  icon={<TrashIcon />}
                  onClick={(e) => {
                    e.preventDefault();
                    void dispatch(removeDraw({ groupId: group.id, drawId: draw.id }))
                      .then(unwrapResult)
                      .then(() => toast.success('Successfully removed draw.'));
                  }}
                />
              </List.Item>
            ))}
          </List>
        )}

        <Button
          title={`Conduct Draw`}
          disabled={!group.canConductDraw}
          variant="large"
          onClick={() => {
            dispatch(startDraw());
            navigate(`/remote/${group.id}/draws/conduct/participants`);
          }}
        />

        {!group.canConductDraw && (
          <p style={{ margin: 0, textAlign: 'center' }}>Remove this year&apos;s draw to conduct a new one.</p>
        )}
      </Content>
    </>
  );
};
