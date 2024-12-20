import { useMemo, useState } from 'react';
import { useLoaderData, useNavigate } from 'react-router-dom';
import Snowfall from 'react-snowfall';
import styled from 'styled-components';

import HomeIcon from '@/assets/home.svg?react';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { FooterAction } from '@/components/FooterAction';
import { Header } from '@/components/Header';
import { LocalDraw } from '@/types';

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
`;

const MerryChristmas = styled.div`
  padding: 10rem 0 7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: ${({ theme }) => theme.typography.size.l};
  font-weight: ${({ theme }) => theme.typography.weight.bold};
`;

export const ShowLocalDraw = () => {
  const draw = useLoaderData() as LocalDraw;
  const allocations = useMemo(() => Object.entries(draw.allocation), [draw]);
  const [position, setPosition] = useState(0);
  const [isRevealed, setIsRevealed] = useState(false);
  const [isComplete, setIsComplete] = useState(false);
  const navigate = useNavigate();

  if (isComplete) {
    return (
      <>
        <Header
          title={draw.entry.title}
          icon={<HomeIcon />}
          onClick={() => {
            navigate('/');
          }}
        />
        <MerryChristmas>🎄 Merry Christmas!</MerryChristmas>
        <Snowfall />
        <FooterAction
          title="Replay"
          onClick={() => {
            setIsComplete(false);
          }}
        />
      </>
    );
  }

  const [participant, recipient] = allocations[position];

  return (
    <>
      <Header
        title={draw.entry.title}
        icon={<HomeIcon />}
        onClick={() => {
          navigate('/');
        }}
      />
      <Content>
        <Description>Hey, {participant} should be the only one looking at the screen!</Description>
        <Result>
          <Name>{participant}</Name>
          <span>you&apos;re Secret Santa for...</span>
          <RevealName key={recipient} $isRevealed={isRevealed}>
            {isRevealed ? recipient : 'SECRET'}
          </RevealName>
        </Result>
      </Content>
      {!isRevealed ? (
        <FooterAction
          title="Reveal"
          onClick={() => {
            setIsRevealed(true);
          }}
        />
      ) : (
        <FooterAction
          onClick={() => {
            if (position + 1 > allocations.length - 1) {
              setIsComplete(true);
              setIsRevealed(false);
              setPosition(0);
              return;
            }
            setPosition(position + 1);
            setIsRevealed(false);
          }}
          title="Memorised"
        />
      )}
    </>
  );
};
