import { unwrapResult } from '@reduxjs/toolkit';
import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';

import TrashIcon from '@/assets/trash.svg?react';
import { BackIcon } from '@/components/BackIcon';
import { Button } from '@/components/Button';
import { ConfirmDialog } from '@/components/ConfirmDialog';
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
  const [drawToRemove, setDrawToRemove] = useState<{ groupId: string; drawId: string; year: number } | null>(null);

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
                    setDrawToRemove({ groupId: group.id, drawId: draw.id, year: draw.year });
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
            dispatch(startDraw({ prefill: group.previousYearsDrawPrefill }));
            navigate(`/remote/${group.id}/draws/conduct/participants`);
          }}
        />

        {!group.canConductDraw && (
          <p style={{ margin: 0, textAlign: 'center' }}>Remove this year&apos;s draw to conduct a new one.</p>
        )}
      </Content>

      <ConfirmDialog
        isOpen={drawToRemove !== null}
        title="Remove Draw?"
        message={`The ${drawToRemove?.year} draw will be permanently deleted from ${group.title}.`}
        onConfirm={() => {
          if (drawToRemove) {
            void dispatch(removeDraw({ groupId: drawToRemove.groupId, drawId: drawToRemove.drawId }))
              .then(unwrapResult)
              .then(() => {
                toast.success('Successfully removed draw.');
                setDrawToRemove(null);
              })
              .catch(() => {
                setDrawToRemove(null);
              });
          }
        }}
        onCancel={() => {
          setDrawToRemove(null);
        }}
        confirmText="Remove"
        cancelText="Keep It"
      />
    </>
  );
};
