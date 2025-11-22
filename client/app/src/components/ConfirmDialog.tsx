import { ReactNode, useEffect, useRef } from 'react';
import styled, { keyframes } from 'styled-components';

import useOnClickOutside from '@/hooks/useOnClickOutside';

const ANIMATION_DURATION = '300ms';
const ANIMATION_DELAY = '100ms';

const shake = keyframes`
  0%, 100% { transform: translateX(0) scale(1); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-2px) scale(1); }
  20%, 40%, 60%, 80% { transform: translateX(2px) scale(1); }
`;

const Overlay = styled.div<{ $isOpen: boolean }>`
  position: fixed;
  visibility: ${({ $isOpen }) => ($isOpen ? 'visible' : 'hidden')};
  inset: 0;
  background: ${({ $isOpen }) => ($isOpen ? 'rgba(0, 0, 0, 60%)' : 'rgba(0, 0, 0, 0%)')};
  backdrop-filter: ${({ $isOpen }) => ($isOpen ? 'blur(4px)' : 'blur(0px)')};
  -webkit-backdrop-filter: ${({ $isOpen }) => ($isOpen ? 'blur(4px)' : 'blur(0px)')};
  transition: ${({ $isOpen }) => ($isOpen ? ANIMATION_DURATION : `${ANIMATION_DURATION} ${ANIMATION_DELAY}`)};
  touch-action: none;
  pointer-events: ${({ $isOpen }) => ($isOpen ? 'auto' : 'none')};
  z-index: 1000;
`;

const Modal = styled.div<{ $isOpen: boolean }>`
  position: fixed;
  overflow: hidden;
  margin: auto;
  display: flex;
  flex-direction: column;
  background: ${({ theme }) => theme.colors.background};
  border: 3px solid ${({ theme }) => theme.colors.text};
  inset: 0;
  max-width: min(90vw, 420px);
  max-height: fit-content;
  padding: ${({ theme }) => theme.spacing.padding.xl};
  gap: ${({ theme }) => theme.spacing.padding.xl};
  transform: ${({ $isOpen }) => ($isOpen ? 'translateY(0) scale(1)' : 'translateY(-20px) scale(0.95)')};
  opacity: ${({ $isOpen }) => ($isOpen ? '1' : '0')};
  pointer-events: ${({ $isOpen }) => ($isOpen ? 'auto' : 'none')};
  transition: ${({ $isOpen }) =>
    $isOpen
      ? `transform ${ANIMATION_DURATION} ${ANIMATION_DELAY}, opacity ${ANIMATION_DURATION} ${ANIMATION_DELAY}`
      : `transform ${ANIMATION_DURATION}, opacity ${ANIMATION_DURATION}`};
  z-index: 1001;
  box-shadow:
    2px 2px 0 0 ${({ theme }) => theme.colors.text},
    4px 4px 0 0 ${({ theme }) => theme.colors.text},
    6px 6px 0 0 ${({ theme }) => theme.colors.text},
    8px 8px 0 0 ${({ theme }) => theme.colors.text};
  animation: ${({ $isOpen }) => ($isOpen ? shake : 'none')} 0.5s ease-in-out ${ANIMATION_DELAY};

  @media (width < 400px) {
    padding: ${({ theme }) => theme.spacing.padding.l};
    gap: ${({ theme }) => theme.spacing.padding.l};
  }
`;

const Title = styled.h3`
  margin: 0;
  padding: 0;
  font-size: ${({ theme }) => theme.typography.size.l};
  font-weight: ${({ theme }) => theme.typography.weight.extrabold};
  color: ${({ theme }) => theme.colors.text};
  text-align: center;
  line-height: 1.3;
  letter-spacing: 0.02em;
`;

const Message = styled.p`
  margin: 0;
  padding: 0;
  font-size: ${({ theme }) => theme.typography.size.m};
  color: ${({ theme }) => theme.colors.text};
  text-align: center;
  line-height: 1.6;
  opacity: 0.95;
`;

const Actions = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${({ theme }) => theme.spacing.padding.m};
  margin-top: ${({ theme }) => theme.spacing.padding.s};

  @media (width >= 400px) {
    flex-direction: row;
  }
`;

const ActionButton = styled.button<{ $variant: 'confirm' | 'cancel' }>`
  flex: 1;
  box-sizing: border-box;
  background: ${({ $variant, theme }) => ($variant === 'confirm' ? theme.colors.text : theme.colors.background)};
  color: ${({ $variant, theme }) => ($variant === 'confirm' ? theme.colors.background : theme.colors.text)};
  border: 2px solid ${({ theme }) => theme.colors.text};
  font-weight: ${({ theme }) => theme.typography.weight.bold};
  font-size: ${({ theme }) => theme.typography.size.m};
  padding: ${({ theme }) => theme.spacing.padding.m} ${({ theme }) => theme.spacing.padding.l};
  cursor: pointer;
  outline: none;
  letter-spacing: 0.02em;
  -webkit-tap-highlight-color: transparent;
  box-shadow:
    1px 1px 0 0 ${({ $variant }) => ($variant === 'confirm' ? '#000' : 'transparent')},
    2px 2px 0 0 ${({ $variant }) => ($variant === 'confirm' ? '#000' : 'transparent')},
    3px 3px 0 0 ${({ $variant }) => ($variant === 'confirm' ? '#000' : 'transparent')},
    4px 4px 0 0 ${({ $variant }) => ($variant === 'confirm' ? '#000' : 'transparent')};
  transition:
    box-shadow 150ms,
    transform 150ms,
    opacity 150ms;

  &:active {
    transform: ${({ $variant }) => ($variant === 'confirm' ? 'translateY(2px) translateX(2px)' : 'none')};
    box-shadow: ${({ $variant }) => ($variant === 'confirm' ? '1px 1px 0 0 #000' : 'none')};
    opacity: 0.8;
  }

  &:hover {
    opacity: ${({ $variant }) => ($variant === 'cancel' ? '0.9' : '1')};
  }
`;

type Props = {
  isOpen: boolean;
  title?: string;
  message: string | ReactNode;
  onConfirm: () => void;
  onCancel: () => void;
  confirmText?: string;
  cancelText?: string;
};

export const ConfirmDialog = ({
  isOpen,
  title = 'Are you sure?',
  message,
  onConfirm,
  onCancel,
  confirmText = 'Confirm',
  cancelText = 'Cancel',
}: Props) => {
  const modalRef = useRef<HTMLDivElement | null>(null);

  useOnClickOutside(modalRef, onCancel);

  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }

    return () => {
      document.body.style.overflow = '';
    };
  }, [isOpen]);

  return (
    <>
      <Overlay $isOpen={isOpen} />
      <Modal $isOpen={isOpen} ref={modalRef}>
        <Title>{title}</Title>
        <Message>{message}</Message>
        <Actions>
          <ActionButton $variant="cancel" onClick={onCancel}>
            {cancelText}
          </ActionButton>
          <ActionButton $variant="confirm" onClick={onConfirm}>
            {confirmText}
          </ActionButton>
        </Actions>
      </Modal>
    </>
  );
};
