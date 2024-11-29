import { unwrapResult } from '@reduxjs/toolkit';
import { useEffect, useState } from 'react';
import Linkify from 'react-linkify';
import { useNavigate, useParams } from 'react-router-dom';
import Snowfall from 'react-snowfall';
import { toast } from 'react-toastify';
import styled from 'styled-components';

import HomeIcon from '@/assets/home.svg?react';
import ShareIcon from '@/assets/share.svg?react';
import { BackIcon } from '@/components/BackIcon';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Header } from '@/components/Header';
import { List } from '@/components/List';
import { Loading } from '@/components/Loading';
import TextField from '@/components/TextField';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { remoteDrawsSelector } from '@/store/remoteDraws';
import { fetchDraw, provideIdeas } from '@/store/remoteDraws/actions';
import { userSelector } from '@/store/user';
import { RemoteAllocation, RemoteDraw } from '@/types';

const FromIdeas = styled.p`
  white-space: pre-wrap;
  word-break: break-word;

  a {
    color: ${({ theme }) => theme.colors.text};
  }
`;

const Name = styled.div`
  font-size: ${({ theme }) => theme.typography.size.l};
  font-weight: ${({ theme }) => theme.typography.weight.bold};
  line-height: ${({ theme }) => theme.typography.size.l};
`;

const RevealName = styled.div<{ $isRevealed: boolean }>`
  font-size: ${({ theme }) => theme.typography.size.l};
  font-weight: ${({ theme }) => theme.typography.weight.bold};
  line-height: ${({ theme }) => theme.typography.size.l};
  transition: filter 1s ease-out;
  filter: ${({ $isRevealed }) => ($isRevealed ? 'blur(0px)' : 'blur(6px)')};
  will-change: filter;
`;

const Result = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: ${({ theme }) => theme.spacing.padding.l};
  margin-bottom: 1rem;
`;

const Description = styled.p`
  line-height: 1.25rem;
  padding: 1rem 1.2rem;
  border-radius: 1rem;
  background-color: #aa0425;
`;

const Allocation = ({
  allocation,
  onIdeasSave,
}: {
  allocation: RemoteAllocation;
  onIdeasSave: (ideas: string) => void;
}) => {
  const [isRevealed, setIsRevealed] = useState(false);
  const [ideas, setIdeas] = useState(allocation.fromIdeas);

  return (
    <>
      <Result>
        <Name>{allocation.from}</Name>
        <span>you&apos;re Secret Santa for...</span>
        <RevealName key={allocation.to} $isRevealed={isRevealed}>
          {isRevealed ? allocation.to : 'SECRET'}
        </RevealName>
      </Result>
      {!isRevealed && (
        <Button
          title="Reveal"
          variant="large"
          onClick={() => {
            setIsRevealed(true);
          }}
        />
      )}
      {isRevealed && (
        <>
          <div>
            <h3>Ideas from {allocation.to}...</h3>
            <Linkify>
              <FromIdeas>{allocation.toIdeas || `${allocation.to} has not provided any ideas yet.`}</FromIdeas>
            </Linkify>
          </div>
          <div>
            <h3>Ideas for your Secret Santa...</h3>
            <TextField
              value={ideas}
              onChange={(event) => {
                setIdeas(event.target.value);
              }}
              placeholder="Ideas"
            />
            <Button
              style={{ marginTop: '1rem' }}
              title="Save Ideas"
              variant="large"
              onClick={() => {
                onIdeasSave(ideas);
              }}
            />
          </div>
          <Snowfall />
        </>
      )}
    </>
  );
};

const AllocationsList = ({ id, allocations }: { id: RemoteDraw['id']; allocations: RemoteAllocation[] }) => {
  return (
    <div>
      <p>Access a participants allocation below:</p>
      <List>
        {allocations.map((allocation) => (
          <List.Item key={allocation.id}>
            <List.Title
              to=""
              onClick={() => {
                window.location.href = `/remote/draws/${id}?token=${allocation.token}`;
              }}
            >
              {allocation.from}
            </List.Title>
            <Button
              icon={<ShareIcon />}
              onClick={(e) => {
                e.preventDefault();
                window.location.href = `/remote/draws/${id}?token=${allocation.token}`;
              }}
            />
          </List.Item>
        ))}
      </List>
    </div>
  );
};

export const ShowRemoteDraw = () => {
  const { id = '' } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const user = useAppSelector(userSelector);
  const { draw, isLoadingDraw } = useAppSelector(remoteDrawsSelector);

  useEffect(() => {
    void dispatch(fetchDraw({ id }));
  }, [id, dispatch]);

  if (isLoadingDraw) {
    return <Loading />;
  }

  if (!draw) return null;

  return (
    <>
      <Header
        title={draw.title}
        icon={user.canRegister ? <HomeIcon /> : <BackIcon />}
        onClick={() => {
          navigate(user.canRegister ? '/' : `/remote/${draw.groupId}`);
        }}
      />
      <Content>
        <Description>
          {draw.description || 'The draw has taken place, find out who you are Secret Santa for below.'}
        </Description>

        {draw.allocation && (
          <Allocation
            allocation={draw.allocation}
            onIdeasSave={(ideas: string) => {
              void dispatch(provideIdeas({ id, ideas }))
                .then(unwrapResult)
                .then(() => {
                  toast.success('Successfully provided ideas.');
                });
            }}
          />
        )}

        {draw.allocations.length > 0 && <AllocationsList id={id} allocations={draw.allocations} />}

        {user.canRegister && (
          <div style={{ marginTop: '2rem' }}>
            <div>Want to conduct your own Secret Santa draw?</div>
            <Button
              style={{ marginTop: '1rem' }}
              title="Register"
              variant="large"
              onClick={() => {
                navigate('/register');
              }}
            />
          </div>
        )}
      </Content>
    </>
  );
};
