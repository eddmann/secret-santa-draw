import { Link, useNavigate } from 'react-router-dom';
import styled from 'styled-components';

import HomeIcon from '@/assets/home.svg?react';
import TrashIcon from '@/assets/trash.svg?react';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Description } from '@/components/Description';
import { Header } from '@/components/Header';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { drawsSelector, removeDraw } from '@/store/localDraws';

const Draw = styled.div`
  display: flex;
`;

const Title = styled(Link)`
  flex: 1;
  color: ${({ theme }) => theme.colors.text};
  text-decoration: none;
  -webkit-tap-highlight-color: transparent;

  span {
    opacity: 0.5;
  }
`;

export const ListLocalDraws = () => {
  const draws = useAppSelector(drawsSelector);
  const dispatch = useAppDispatch();
  const navigate = useNavigate();

  return (
    <>
      <Header
        title="Local"
        icon={<HomeIcon />}
        onClick={() => {
          navigate('/');
        }}
      />
      <Content>
        <Description>Previous local draws that have taken place are listed below:</Description>

        {draws.map((draw) => (
          <Draw key={draw.id}>
            <Title to={`/local/draws/${draw.id}`}>{draw.entry.title}</Title>
            <Button
              icon={<TrashIcon />}
              onClick={(e) => {
                e.preventDefault();
                dispatch(removeDraw(draw.id));
              }}
            />
          </Draw>
        ))}

        <Button
          title="Conduct Draw"
          variant="large"
          onClick={() => {
            navigate(`/local/draws/conduct/participants`);
          }}
        />
      </Content>
    </>
  );
};
