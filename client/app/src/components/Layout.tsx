import { Outlet } from 'react-router-dom';
import styled from 'styled-components';

const Root = styled.div`
  max-width: 600px;
  margin: 0 auto;
  padding: env(safe-area-inset-top) ${({ theme }) => theme.spacing.padding.m} 0;

  @media (width >= 800px), (orientation: landscape) {
    padding: 0 max(${({ theme }) => theme.spacing.padding.m}, env(safe-area-inset-right)) env(safe-area-inset-bottom)
      max(${({ theme }) => theme.spacing.padding.m}, env(safe-area-inset-left));
  }
`;

export const Layout = () => {
  return (
    <Root>
      <Outlet />
    </Root>
  );
};
