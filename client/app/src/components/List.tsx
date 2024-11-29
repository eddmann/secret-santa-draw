import { PropsWithChildren } from 'react';
import { Link } from 'react-router-dom';
import styled from 'styled-components';

export const List = ({ children }: PropsWithChildren) => <div>{children}</div>;

List.Item = styled.div`
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;

  &:last-child {
    margin-bottom: 0;
  }
`;

List.Title = styled(Link)`
  flex: 1;
  color: ${({ theme }) => theme.colors.text};
  text-decoration: none;
  -webkit-tap-highlight-color: transparent;

  span {
    opacity: 0.5;
  }
`;
