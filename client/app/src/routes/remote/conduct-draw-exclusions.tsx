import { unwrapResult } from '@reduxjs/toolkit';
import { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import styled, { css, keyframes } from 'styled-components';

import { BackIcon } from '@/components/BackIcon';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { FooterAction } from '@/components/FooterAction';
import { Header } from '@/components/Header';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { remoteEntrySelector, toggleExclusion } from '@/store/remoteEntry';
import { draw } from '@/store/remoteEntry/actions';
import { RemoteParticipant } from '@/types';

const Participants = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${({ theme }) => theme.spacing.padding.m};
`;

const Exclusion = styled.div`
  display: flex;
  gap: ${({ theme }) => theme.spacing.padding.m};
`;

const ExclusionButton = styled.button<{ $isExcluded: boolean }>`
  background: ${({ theme }) => theme.colors.text};
  border: 2px solid #000;
  font-size: ${({ theme }) => theme.typography.size.m};
  outline: none;
  letter-spacing: 0.01rem;
  color: #000;
  line-height: ${({ theme }) => theme.typography.size.m};
  padding: ${({ theme }) => theme.spacing.padding.s};
  cursor: pointer;
  box-shadow:
    1px 1px 0 0,
    2px 2px 0 0,
    3px 3px 0 0,
    4px 4px 0 0,
    5px 5px 0 0;
  transition:
    box-shadow 150ms,
    transform 150ms;

  &:active {
    transform: translateY(2px) translateX(2px);
    box-shadow: none;
  }

  ${({ $isExcluded }) =>
    $isExcluded &&
    css`
      text-decoration: line-through;
    `}
`;

const Number = styled.div`
  background: rgba(0 0 0 / 15%);
  padding: ${({ theme }) => theme.spacing.padding.s};
  line-height: ${({ theme }) => theme.typography.size.s};
  border-radius: 50%;
  width: 1rem;
  height: 1rem;
  text-align: center;
`;

const ExclusionDetails = styled.div`
  overflow: hidden;
`;

const Name = styled.div`
  margin-top: ${({ theme }) => theme.spacing.padding.xs};
`;

const ExclusionList = styled.div`
  display: flex;
  gap: ${({ theme }) => theme.spacing.padding.m};
  margin-top: ${({ theme }) => theme.spacing.padding.m};
  overflow-y: scroll;
  padding: 0 ${({ theme }) => theme.spacing.padding.m} ${({ theme }) => theme.spacing.padding.m} 0;

  &::-webkit-scrollbar {
    display: none;
  }
`;

const shake = keyframes`
  0% { transform: translateX(0) }
  25%, 75% { transform: translateX(5px) }
  50% { transform: translateX(-5px) }
  100% { transform: translateX(0) }
`;

const DrawFooterAction = styled(FooterAction)<{ $hasError: boolean }>`
  ${({ $hasError }) =>
    $hasError &&
    css`
      animation: ${shake} 250ms ease;
    `}
`;

export const ConductRemoteDrawExclusions = () => {
  const { id = '' } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const remoteEntry = useAppSelector(remoteEntrySelector);

  useEffect(() => {
    if (remoteEntry.description === '' || remoteEntry.participants.length < 3) {
      navigate(`/remote/${id}/draws/conduct/participants`);
    }
  }, [remoteEntry, navigate, id]);

  const handleNext = () => {
    const { description, participants, exclusions } = remoteEntry;
    void dispatch(draw({ id, description, participants, exclusions }))
      .then(unwrapResult)
      .then(() => {
        toast.success('Successfully conducted draw.');
        navigate(`/remote/${id}`);
      });
  };

  const handleExclusion =
    (participantEmail: RemoteParticipant['email'], exclusionEmail: RemoteParticipant['email']) => () => {
      dispatch(toggleExclusion({ participantEmail, exclusionEmail }));
    };

  return (
    <>
      <Header
        title="Exclusions"
        icon={<BackIcon />}
        onClick={() => {
          navigate(`/remote/${id}/draws/conduct/participants`);
        }}
      />
      <Content>
        <Description>Should someone not be a participants Secret Santa?</Description>

        <Participants>
          {remoteEntry.participants.map(({ name, email }, idx) => (
            <Exclusion key={email}>
              <div>
                <Number>{idx + 1}</Number>
              </div>
              <ExclusionDetails>
                <Name>{name}</Name>
                <ExclusionList>
                  {remoteEntry.participants
                    .filter((participant) => participant.email !== email)
                    .map((exclusion) => (
                      <ExclusionButton
                        key={exclusion.email}
                        onClick={handleExclusion(email, exclusion.email)}
                        $isExcluded={(remoteEntry.exclusions[email] ?? []).includes(exclusion.email)}
                      >
                        {exclusion.name}
                      </ExclusionButton>
                    ))}
                </ExclusionList>
              </ExclusionDetails>
            </Exclusion>
          ))}
        </Participants>
      </Content>
      <DrawFooterAction $hasError={false} title={remoteEntry.isDrawing ? 'Drawing' : 'Draw'} onClick={handleNext} />
    </>
  );
};
