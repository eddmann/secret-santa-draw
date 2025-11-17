import { unwrapResult } from '@reduxjs/toolkit';
import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import Snowfall from 'react-snowfall';
import { toast } from 'react-toastify';
import styled from 'styled-components';

import HomeIcon from '@/assets/home.svg?react';
import ShareIcon from '@/assets/share.svg?react';
import { BackIcon } from '@/components/BackIcon';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { GiftIdeasDisplay } from '@/components/GiftIdeasDisplay';
import { GiftIdeasEditor } from '@/components/GiftIdeasEditor';
import { Header } from '@/components/Header';
import { List } from '@/components/List';
import { Loading } from '@/components/Loading';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { remoteDrawsSelector } from '@/store/remoteDraws';
import { fetchDraw, provideIdeas } from '@/store/remoteDraws/actions';
import { userSelector } from '@/store/user';
import { RemoteAllocation, RemoteDraw } from '@/types';

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

const SaveButton = styled(Button)`
  margin-top: 1rem;
`;

const Description = styled.p`
  line-height: 1.25rem;
  padding: 1rem 1.2rem;
  border-radius: 1rem;
  background-color: #aa0425;
`;

const GiftIdeasContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${({ theme }) => theme.spacing.padding.l};
  margin-top: ${({ theme }) => theme.spacing.padding.l};
  width: 100%;
  max-width: 100%;
`;

const GiftIdeaCard = styled.div`
  background: rgb(170 4 37 / 60%);
  border: 2px solid #000;
  border-radius: 0.5rem;
  padding: ${({ theme }) => theme.spacing.padding.l};
  box-shadow: 3px 3px 0 0 rgb(0 0 0 / 20%);
`;

const SectionHeader = styled.h3`
  margin: 0 0 ${({ theme }) => theme.spacing.padding.m} 0;
`;

const Allocation = ({
  allocation,
  onIdeasSave,
  isRevealed,
  onReveal,
}: {
  allocation: RemoteAllocation;
  onIdeasSave: (ideas: string[]) => void;
  isRevealed: boolean;
  onReveal: () => void;
}) => {
  const [ideas, setIdeas] = useState(allocation.fromIdeas);

  useEffect(() => {
    setIdeas(allocation.fromIdeas);
  }, [allocation.fromIdeas]);

  const hasChanges = () => {
    if (ideas.length !== allocation.fromIdeas.length) return true;
    return ideas.some((idea, index) => idea !== allocation.fromIdeas[index]);
  };

  return (
    <>
      <Result>
        <Name>{allocation.from}</Name>
        <span>you&apos;re Secret Santa for...</span>
        <RevealName key={allocation.to} $isRevealed={isRevealed}>
          {isRevealed ? allocation.to : 'SECRET'}
        </RevealName>
      </Result>
      {!isRevealed && <Button title="Reveal" variant="large" onClick={onReveal} />}
      {isRevealed && (
        <>
          <GiftIdeasContainer>
            <GiftIdeaCard>
              <SectionHeader>Ideas from {allocation.to}...</SectionHeader>
              <GiftIdeasDisplay
                ideas={allocation.toIdeas}
                emptyMessage={`${allocation.to} has not provided any ideas yet.`}
              />
            </GiftIdeaCard>
            <GiftIdeaCard>
              <SectionHeader>Ideas for your Secret Santa...</SectionHeader>
              <GiftIdeasEditor ideas={ideas} onChange={setIdeas} disabled={!allocation.canProvideIdeas} />
              {allocation.canProvideIdeas && hasChanges() && (
                <SaveButton
                  title="Save Ideas"
                  variant="large"
                  onClick={() => {
                    onIdeasSave(ideas);
                  }}
                />
              )}
            </GiftIdeaCard>
          </GiftIdeasContainer>
          <Snowfall />
        </>
      )}
    </>
  );
};

const CollapseHeader = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  user-select: none;
  line-height: 1.25rem;
  padding: 1rem 1.2rem;
  border-radius: 1rem;
  background-color: #aa0425;

  &:hover {
    opacity: 0.85;
  }
`;

const CollapseText = styled.span`
  flex: 1;
`;

const Arrow = styled.span<{ $isOpen: boolean }>`
  display: inline-block;
  font-size: 0.8rem;
  transform: ${({ $isOpen }) => ($isOpen ? 'rotate(90deg)' : 'rotate(0deg)')};
  margin-left: ${({ theme }) => theme.spacing.padding.s};
`;

const CollapseContent = styled.div<{ $isOpen: boolean }>`
  display: ${({ $isOpen }) => ($isOpen ? 'block' : 'none')};
  margin-top: ${({ $isOpen }) => ($isOpen ? '1rem' : '0')};
  padding-left: 1.2rem;
  padding-right: 1.2rem;
`;

const AllocationsListWrapper = styled.div`
  margin-top: 2rem;
`;

const RegisterPrompt = styled.div`
  margin-top: 2rem;
`;

const RegisterButton = styled(Button)`
  margin-top: 1rem;
`;

const AllocationsList = ({ id, allocations }: { id: RemoteDraw['id']; allocations: RemoteAllocation[] }) => {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <AllocationsListWrapper>
      <CollapseHeader
        onClick={() => {
          setIsOpen(!isOpen);
        }}
      >
        <CollapseText>Access a participants allocation</CollapseText>
        <Arrow $isOpen={isOpen}>▶</Arrow>
      </CollapseHeader>
      <CollapseContent $isOpen={isOpen}>
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
      </CollapseContent>
    </AllocationsListWrapper>
  );
};

export const ShowRemoteDraw = () => {
  const { id = '' } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const user = useAppSelector(userSelector);
  const { draw, isLoadingDraw } = useAppSelector(remoteDrawsSelector);
  const [isRevealed, setIsRevealed] = useState(false);

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
            isRevealed={isRevealed}
            onReveal={() => {
              setIsRevealed(true);
            }}
            onIdeasSave={(ideas: string[]) => {
              // Optimistically update the allocation in the component
              // The backend sync happens in the background
              void dispatch(provideIdeas({ id, ideas }))
                .then(unwrapResult)
                .then(() => {
                  toast.success('Successfully provided ideas.');
                })
                .catch(() => {
                  // Error is already handled in the action, just refetch to revert
                  void dispatch(fetchDraw({ id }));
                });
            }}
          />
        )}

        {draw.allocations.length > 0 && <AllocationsList id={id} allocations={draw.allocations} />}

        {user.canRegister && (
          <RegisterPrompt>
            <div>Want to conduct your own Secret Santa draw?</div>
            <RegisterButton
              title="Register"
              variant="large"
              onClick={() => {
                navigate('/register');
              }}
            />
          </RegisterPrompt>
        )}
      </Content>
    </>
  );
};
