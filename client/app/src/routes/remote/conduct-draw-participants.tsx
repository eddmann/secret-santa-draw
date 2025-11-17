import { useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import styled, { css, keyframes } from 'styled-components';

import CloseIcon from '@/assets/close.svg?react';
import { BackIcon } from '@/components/BackIcon';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { FooterAction } from '@/components/FooterAction';
import { Header } from '@/components/Header';
import InputField from '@/components/InputField';
import TextField from '@/components/TextField';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { addParticipant, remoteEntrySelector, removeParticipant, updateDescription } from '@/store/remoteEntry';

const fadeIn = keyframes`
  from { opacity: 0 }
  to { opacity: 1 }
`;

const shake = keyframes`
  0% { transform: translateX(0) }
  25%, 75% { transform: translateX(5px) }
  50% { transform: translateX(-5px) }
  100% { transform: translateX(0) }
`;

const AddParticipantForm = styled.div`
  display: flex;
  gap: ${({ theme }) => theme.spacing.padding.s};
`;

const ParticipantsList = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${({ theme }) => theme.spacing.padding.m};
`;

const Participant = styled.div`
  display: flex;
  align-items: center;
  gap: ${({ theme }) => theme.spacing.padding.m};
  animation: ${fadeIn} 250ms ease;
`;

const RemoveButton = styled(CloseIcon)`
  height: 2rem;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
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

const Name = styled.div`
  width: 100%;
`;

const EntryInputField = styled(InputField)<{ $hasError: boolean }>`
  ${({ $hasError }) =>
    $hasError &&
    css`
      animation: ${shake} 250ms ease;
    `}
`;

export const ConductRemoteDrawParticipants = () => {
  const { id = '' } = useParams();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const remoteEntry = useAppSelector(remoteEntrySelector);
  const isValid = useMemo(() => remoteEntry.description !== '' && remoteEntry.participants.length > 2, [remoteEntry]);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [hasNameError, setHasNameError] = useState(false);
  const [hasEmailError, setHasEmailError] = useState(false);

  const handleAdd = () => {
    if (name === '') {
      setHasNameError(true);
      setTimeout(() => {
        setHasNameError(false);
      }, 250);
      return;
    }

    if (email === '' || remoteEntry.participants.some((participant) => participant.email === email)) {
      setHasEmailError(true);
      setTimeout(() => {
        setHasEmailError(false);
      }, 250);
      return;
    }

    dispatch(addParticipant({ name, email }));
    setName('');
    setEmail('');
    setHasNameError(false);
    setHasEmailError(false);
  };

  return (
    <>
      <Header
        title="Participants"
        icon={<BackIcon />}
        onClick={() => {
          navigate(`/remote/${id}`);
        }}
      />
      <Content>
        <Description>
          Describe your {new Date().getFullYear()} draw (budget, themes etc.), and who is participating in it.
        </Description>

        <TextField
          value={remoteEntry.description}
          onChange={(event) => dispatch(updateDescription(event.target.value))}
          placeholder="Description"
        />

        {remoteEntry.participants.length > 0 && (
          <ParticipantsList>
            {remoteEntry.participants.map(({ name, email }, idx) => (
              <Participant key={email}>
                <div>
                  <Number>{idx + 1}</Number>
                </div>
                <Name>
                  {name}
                  <br />
                  <span style={{ fontSize: '.8em' }}>{email}</span>
                </Name>
                <RemoveButton onClick={() => dispatch(removeParticipant(email))} />
              </Participant>
            ))}
          </ParticipantsList>
        )}
        <AddParticipantForm>
          <EntryInputField
            $hasError={hasNameError}
            placeholder="Name"
            value={name}
            onChange={(e) => {
              setName(e.target.value);
            }}
          />
          <EntryInputField
            $hasError={hasEmailError}
            placeholder="Email"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);
            }}
          />
          <Button onClick={handleAdd} title="Add" />
        </AddParticipantForm>
      </Content>
      {isValid && (
        <FooterAction
          onClick={() => {
            navigate(`/remote/${id}/draws/conduct/exclusions`);
          }}
          title="Next"
        />
      )}
    </>
  );
};
