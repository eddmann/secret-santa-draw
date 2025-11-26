import { unwrapResult } from '@reduxjs/toolkit';
import { useEffect, useRef, useState } from 'react';
import Linkify from 'react-linkify';
import { useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import styled, { css, keyframes } from 'styled-components';

import { BackIcon } from '@/components/BackIcon';
import { Button } from '@/components/Button';
import { Content } from '@/components/Content';
import { Header } from '@/components/Header';
import { Loading } from '@/components/Loading';
import useVisualViewport from '@/hooks/useVisualViewport';
import { allocationMessagesSelector, fetchMessages, sendMessage } from '@/store/allocationMessages';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { fetchDraw, remoteDrawsSelector } from '@/store/remoteDraws';
import { AllocationMessageDirection } from '@/types';

const shake = keyframes`
  0% { transform: translateX(0) }
  25%, 75% { transform: translateX(5px) }
  50% { transform: translateX(-5px) }
  100% { transform: translateX(0) }
`;

const MessageContent = styled(Content)`
  padding: 4.5rem 0 0;
  gap: 0;
`;

const ContentWrapper = styled.div<{ $keyboardHeight: number }>`
  display: flex;
  flex-direction: column;
  height: calc(
    100dvh - 5rem - env(safe-area-inset-top) - env(safe-area-inset-bottom) -
      ${({ $keyboardHeight }) => $keyboardHeight}px
  );
  overflow: hidden;
  transition: height 0.1s ease-out;
`;

const ScrollableContent = styled.div`
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
`;

const MessagesContainer = styled.div`
  display: flex;
  flex-direction: column-reverse;
  gap: ${({ theme }) => theme.spacing.padding.m};
  flex: 1;
  overflow-y: auto;
  padding: ${({ theme }) => theme.spacing.padding.m};
  background: rgb(170 4 37 / 30%);
  border-radius: 0.5rem;
`;

const MessageBubble = styled.div<{ $fromMe: boolean }>`
  max-width: 75%;
  padding: ${({ theme }) => theme.spacing.padding.m};
  border-radius: 1rem;
  background: ${({ $fromMe }) => ($fromMe ? '#aa0425' : 'rgb(255 255 255 / 20%)')};
  align-self: ${({ $fromMe }) => ($fromMe ? 'flex-end' : 'flex-start')};
  box-shadow: 2px 2px 4px rgb(0 0 0 / 20%);
  word-wrap: break-word;
  border: 2px solid ${({ $fromMe }) => ($fromMe ? '#000' : 'rgb(255 255 255 / 30%)')};
`;

const MessageText = styled.p`
  margin: 0;
  line-height: 1.4;
  white-space: pre-wrap;
  word-break: break-word;

  a {
    color: inherit;
    text-decoration: underline;
  }
`;

const MessageTime = styled.div`
  font-size: ${({ theme }) => theme.typography.size.s};
  opacity: 0.7;
  margin-top: ${({ theme }) => theme.spacing.padding.xs};
`;

const InputContainer = styled.div`
  display: flex;
  gap: ${({ theme }) => theme.spacing.padding.s};
  padding: ${({ theme }) => theme.spacing.padding.m} ${({ theme }) => theme.spacing.padding.s};
  padding-bottom: calc(${({ theme }) => theme.spacing.padding.m} + env(safe-area-inset-bottom));
  background: ${({ theme }) => theme.colors.background};
  border-top: 2px solid rgb(0 0 0 / 10%);
`;

const MessageInput = styled.textarea<{ $hasError?: boolean }>`
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
  background: #fff;
  color: #000;
  -webkit-appearance: none;
  resize: none;
  overflow: hidden;
  min-height: 2.5rem;
  box-shadow:
    1px 1px 0 0,
    2px 2px 0 0,
    3px 3px 0 0,
    4px 4px 0 0;

  &:focus {
    outline: none;
  }

  &::placeholder {
    color: rgb(0 0 0 / 40%);
  }

  ${({ $hasError }) =>
    $hasError &&
    css`
      animation: ${shake} 250ms ease;
    `}
`;

const SendButton = styled(Button)`
  align-self: stretch;
`;

const Description = styled.p`
  line-height: 1.25rem;
  padding: 1rem 1.2rem;
  border-radius: 1rem;
  background-color: #aa0425;
  margin-bottom: ${({ theme }) => theme.spacing.padding.l};
`;

const EmptyState = styled.div`
  text-align: center;
  padding: ${({ theme }) => theme.spacing.padding.xl};
  opacity: 0.7;
`;

type MessageConversationProps = {
  direction: AllocationMessageDirection;
};

export const MessageConversation = ({ direction }: MessageConversationProps) => {
  const { id = '' } = useParams();
  const [searchParams] = useSearchParams();
  const accessToken = searchParams.get('token');
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { draw, isLoadingDraw } = useAppSelector(remoteDrawsSelector);
  const [message, setMessage] = useState('');
  const [hasError, setHasError] = useState(false);
  const textareaRef = useRef<HTMLTextAreaElement>(null);
  const inputContainerRef = useRef<HTMLDivElement>(null);

  const { keyboardHeight } = useVisualViewport({
    onKeyboardShow: () => {
      // Scroll input into view when keyboard appears
      setTimeout(() => {
        inputContainerRef.current?.scrollIntoView({ behavior: 'smooth', block: 'end' });
      }, 100);
    },
  });

  const allocationId = draw?.allocation?.id ?? '';
  const { messages, isLoading, isSending } = useAppSelector((state) =>
    allocationMessagesSelector(state, allocationId, direction),
  );

  useEffect(() => {
    void dispatch(fetchDraw({ id }));
  }, [id, dispatch]);

  useEffect(() => {
    if (allocationId) {
      void dispatch(fetchMessages({ allocationId, direction }));
    }
  }, [allocationId, direction, dispatch]);

  const adjustHeight = () => {
    const textarea = textareaRef.current;
    if (textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = `${textarea.scrollHeight}px`;
    }
  };

  const handleSend = () => {
    if (!message.trim()) {
      setHasError(true);
      setTimeout(() => {
        setHasError(false);
      }, 250);
      return;
    }

    if (!allocationId) return;

    void dispatch(sendMessage({ allocationId, direction, message: message.trim() }))
      .then(unwrapResult)
      .then(() => {
        setMessage('');
        setHasError(false);
        toast.success('Message sent.');
        setTimeout(() => {
          if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
          }
        }, 0);
      })
      .catch(() => {
        toast.error('Failed to send message.');
      });
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) {
      e.preventDefault();
      handleSend();
    }
  };

  if (isLoadingDraw || !draw?.allocation) {
    return <Loading />;
  }

  const isToRecipient = direction === 'to-recipient';
  const conversationWith = isToRecipient ? draw.allocation.to : 'your Secret Santa';
  const title = `Message with ${conversationWith}`;

  const formatTime = (timestamp: string) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInHours = (now.getTime() - date.getTime()) / (1000 * 60 * 60);

    if (diffInHours < 24) {
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return date.toLocaleDateString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
  };

  return (
    <>
      <Header
        title={title}
        icon={<BackIcon />}
        onClick={() => {
          const url = accessToken ? `/remote/draws/${id}?token=${accessToken}` : `/remote/draws/${id}`;
          navigate(url);
        }}
      />
      <MessageContent>
        {isLoading ? (
          <Loading />
        ) : (
          <ContentWrapper $keyboardHeight={keyboardHeight}>
            <ScrollableContent>
              <Description>Send anonymous messages, keeping the magic of Secret Santa alive.</Description>

              <MessagesContainer>
                {messages.length === 0 ? (
                  <EmptyState>No messages yet. Start the conversation!</EmptyState>
                ) : (
                  messages.map((msg) => (
                    <MessageBubble key={msg.id} $fromMe={msg.fromMe}>
                      <MessageText>
                        <Linkify
                          componentDecorator={(decoratedHref, decoratedText, key) => (
                            <a href={decoratedHref} key={key} target="_blank" rel="noopener noreferrer">
                              {decoratedText}
                            </a>
                          )}
                        >
                          {msg.message}
                        </Linkify>
                      </MessageText>
                      <MessageTime>{formatTime(msg.createdAt)}</MessageTime>
                    </MessageBubble>
                  ))
                )}
              </MessagesContainer>
            </ScrollableContent>

            <InputContainer ref={inputContainerRef}>
              <MessageInput
                ref={textareaRef}
                value={message}
                onChange={(e) => {
                  setMessage(e.target.value);
                  adjustHeight();
                }}
                onKeyDown={handleKeyDown}
                placeholder={`Type your message to ${conversationWith}...`}
                disabled={isSending}
                maxLength={1000}
                $hasError={hasError}
              />
              <SendButton title="Send" onClick={handleSend} />
            </InputContainer>
          </ContentWrapper>
        )}
      </MessageContent>
    </>
  );
};
