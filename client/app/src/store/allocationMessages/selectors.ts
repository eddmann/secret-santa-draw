import { AllocationMessageDirection } from '@/types';

import { RootState } from '..';

const getConversationKey = (allocationId: string, direction: AllocationMessageDirection) =>
  `${allocationId}:${direction}`;

export const allocationMessagesSelector = (
  state: RootState,
  allocationId: string,
  direction: AllocationMessageDirection,
) => {
  const key = getConversationKey(allocationId, direction);
  return state.allocationMessages.conversations[key] ?? { messages: [], isLoading: false, isSending: false };
};
