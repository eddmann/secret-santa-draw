import { useEffect, useState } from 'react';
import styled from 'styled-components';

import { Content } from './Content';
import { Header } from './Header';

const Wrapper = styled.div`
  text-align: center;
`;

export const Loading = () => {
  const [isLoadingShown, setIsLoadingShown] = useState(false);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoadingShown(true);
    }, 400);
    return () => {
      clearTimeout(timer);
    };
  }, []);

  return (
    isLoadingShown && (
      <>
        <Header title="" />
        <Content>
          <Wrapper>🎁 Loading...</Wrapper>
        </Content>
      </>
    )
  );
};
