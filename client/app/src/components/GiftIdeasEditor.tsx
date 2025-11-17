import { useRef, useState } from 'react';
import Linkify from 'react-linkify';
import styled, { css, keyframes } from 'styled-components';

import ChevronDownIcon from '@/assets/chevron-down.svg?react';
import ChevronUpIcon from '@/assets/chevron-up.svg?react';
import CloseIcon from '@/assets/close.svg?react';
import { Button } from '@/components/Button';
import useBodyLock from '@/hooks/useBodyLock';

type GiftIdeasEditorProps = {
  ideas: string[];
  onChange: (ideas: string[]) => void;
  disabled?: boolean;
  maxIdeas?: number;
  maxLength?: number;
};

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

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${({ theme }) => theme.spacing.padding.m};
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
  animation: ${fadeIn} 250ms ease;
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
`;

const IdeaText = styled.div`
  flex: 1;
  word-break: break-word;
  width: 100%;
  white-space: pre-wrap;
  line-height: 1.5;

  a {
    color: ${({ theme }) => theme.colors.text};
    text-decoration: underline;
  }
`;

const Controls = styled.div`
  display: flex;
  gap: ${({ theme }) => theme.spacing.padding.s};
  align-items: center;
`;

const MoveButton = styled.button`
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  height: 2rem;
  width: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  -webkit-tap-highlight-color: transparent;

  &:disabled {
    opacity: 0.2;
    cursor: not-allowed;
  }
`;

const MoveUpIcon = styled(ChevronUpIcon)`
  height: 2rem;
`;

const MoveDownIcon = styled(ChevronDownIcon)`
  height: 2rem;
`;

const RemoveButton = styled(CloseIcon)`
  height: 2rem;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
`;

const InputContainer = styled.div`
  display: flex;
  gap: ${({ theme }) => theme.spacing.padding.s};
`;

const TextInput = styled.textarea<{ disabled?: boolean; $hasError?: boolean }>`
  font-family: ${({ theme }) => theme.typography.type};
  box-sizing: border-box;
  font-size: 1.2rem;
  flex: 1;
  outline: none;
  border-radius: 0;
  padding: ${({ theme }) => theme.spacing.padding.s};
  letter-spacing: 0.05em;
  -webkit-tap-highlight-color: transparent;
  border: 2px solid #000;
  -webkit-appearance: none;
  resize: none;
  overflow: hidden;
  min-height: 2.5rem;
  box-shadow:
    1px 1px 0 0,
    2px 2px 0 0,
    3px 3px 0 0,
    4px 4px 0 0;
  opacity: ${({ disabled }) => (disabled ? '0.5' : '1')};
  cursor: ${({ disabled }) => (disabled ? 'not-allowed' : 'text')};
  ${({ $hasError }) =>
    $hasError &&
    css`
      animation: ${shake} 250ms ease;
    `}
`;

const MaxMessage = styled.p`
  font-size: 0.85rem;
  opacity: 0.6;
  margin: 0;
`;

const HelpText = styled.p`
  font-size: 0.95rem;
  opacity: 0.75;
  margin: 0 0 ${({ theme }) => theme.spacing.padding.m} 0;
  line-height: 1.4;
`;

export const GiftIdeasEditor = ({
  ideas,
  onChange,
  disabled = false,
  maxIdeas = 5,
  maxLength = 500,
}: GiftIdeasEditorProps) => {
  const [newIdea, setNewIdea] = useState('');
  const [hasError, setHasError] = useState(false);
  const textareaRef = useRef<HTMLTextAreaElement>(null);
  const { lock, unlock } = useBodyLock();

  const adjustHeight = () => {
    const textarea = textareaRef.current;
    if (textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = `${textarea.scrollHeight}px`;
    }
  };

  const handleAdd = () => {
    if (!newIdea.trim()) {
      setHasError(true);
      setTimeout(() => {
        setHasError(false);
      }, 250);
      return;
    }

    if (ideas.length < maxIdeas) {
      onChange([...ideas, newIdea.trim()]);
      setNewIdea('');
      setHasError(false);
      setTimeout(() => {
        if (textareaRef.current) {
          textareaRef.current.style.height = 'auto';
        }
      }, 0);
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) {
      e.preventDefault();
      handleAdd();
    }
  };

  const handleRemove = (index: number) => {
    onChange(ideas.filter((_, i) => i !== index));
  };

  const handleMoveUp = (index: number) => {
    if (index === 0) return;
    const newIdeas = [...ideas];
    [newIdeas[index - 1], newIdeas[index]] = [newIdeas[index], newIdeas[index - 1]];
    onChange(newIdeas);
  };

  const handleMoveDown = (index: number) => {
    if (index === ideas.length - 1) return;
    const newIdeas = [...ideas];
    [newIdeas[index], newIdeas[index + 1]] = [newIdeas[index + 1], newIdeas[index]];
    onChange(newIdeas);
  };

  return (
    <Container>
      {!disabled && (
        <HelpText>
          Give your Secret Santa a helping hand! Add your gift ideas below and order them by preference (most wanted
          first).
        </HelpText>
      )}
      {ideas.length > 0 && (
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
              {!disabled && (
                <Controls>
                  <MoveButton
                    onClick={() => {
                      handleMoveUp(index);
                    }}
                    disabled={index === 0}
                    title="Move up"
                  >
                    <MoveUpIcon />
                  </MoveButton>
                  <MoveButton
                    onClick={() => {
                      handleMoveDown(index);
                    }}
                    disabled={index === ideas.length - 1}
                    title="Move down"
                  >
                    <MoveDownIcon />
                  </MoveButton>
                  <RemoveButton
                    onClick={() => {
                      handleRemove(index);
                    }}
                  />
                </Controls>
              )}
            </IdeaItem>
          ))}
        </IdeaList>
      )}

      {!disabled && ideas.length < maxIdeas && (
        <InputContainer>
          <TextInput
            ref={textareaRef}
            placeholder="Add a gift idea (text or URL)"
            value={newIdea}
            onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => {
              setNewIdea(e.target.value);
              adjustHeight();
            }}
            onKeyDown={handleKeyDown}
            maxLength={maxLength}
            onFocus={lock}
            onBlur={unlock}
            $hasError={hasError}
          />
          <Button onClick={handleAdd} title="Add" />
        </InputContainer>
      )}

      {!disabled && ideas.length >= maxIdeas && (
        <MaxMessage>Maximum of {maxIdeas} ideas reached, please remove one to add another.</MaxMessage>
      )}
    </Container>
  );
};
