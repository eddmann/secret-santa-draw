import Linkify from 'react-linkify';
import styled from 'styled-components';

type GiftIdeasDisplayProps = {
  ideas: string[];
  emptyMessage?: string;
};

const EmptyMessage = styled.p`
  font-style: italic;
  opacity: 0.6;
  margin: 0;
`;

const HelpText = styled.p`
  font-size: 0.95rem;
  opacity: 0.75;
  margin: 0 0 ${({ theme }) => theme.spacing.padding.m} 0;
  line-height: 1.4;
`;

const IdeaList = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${({ theme }) => theme.spacing.padding.m};
`;

const IdeaItem = styled.div`
  display: flex;
  align-items: flex-start;
  gap: ${({ theme }) => theme.spacing.padding.m};
  padding-bottom: ${({ theme }) => theme.spacing.padding.m};
  border-bottom: 1px solid rgba(0 0 0 / 15%);

  &:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  @media (width <= 768px) {
    flex-wrap: wrap;
  }
`;

const Number = styled.div`
  background: rgba(0 0 0 / 15%);
  padding: ${({ theme }) => theme.spacing.padding.s};
  line-height: ${({ theme }) => theme.typography.size.s};
  border-radius: 50%;
  width: 1rem;
  height: 1rem;
  text-align: center;
  flex-shrink: 0;

  @media (width <= 768px) {
    display: none;
  }
`;

const IdeaText = styled.div`
  word-break: break-word;
  width: 100%;
  white-space: pre-wrap;
  line-height: 1.5;

  a {
    color: ${({ theme }) => theme.colors.text};
    text-decoration: underline;
  }

  @media (width <= 768px) {
    flex-basis: 100%;
  }
`;

export const GiftIdeasDisplay = ({ ideas, emptyMessage = 'No gift ideas provided yet.' }: GiftIdeasDisplayProps) => {
  if (ideas.length === 0) {
    return <EmptyMessage>{emptyMessage}</EmptyMessage>;
  }

  return (
    <>
      <HelpText>Gift ideas ordered by preference:</HelpText>
      <IdeaList>
        {ideas.map((idea, index) => (
          <IdeaItem key={index}>
            <Number>{index + 1}</Number>
            <IdeaText>
              <Linkify
                componentDecorator={(decoratedHref, decoratedText, key) => (
                  <a href={decoratedHref} key={key} target="_blank" rel="noopener noreferrer">
                    {decoratedText}
                  </a>
                )}
              >
                {idea}
              </Linkify>
            </IdeaText>
          </IdeaItem>
        ))}
      </IdeaList>
    </>
  );
};
