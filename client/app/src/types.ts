export type LocalParticipant = string;

export type LocalEntry = {
  title: string;
  participants: LocalParticipant[];
  exclusions: Record<LocalParticipant, LocalParticipant[] | undefined>;
};

export type UnixTimestamp = number;

export type LocalAllocation = Record<LocalParticipant, LocalParticipant>;

export type LocalDraw = {
  id: string;
  entry: LocalEntry;
  allocation: LocalAllocation;
  at: UnixTimestamp;
};

export type RemoteGroup = { id: string; title: string };

export type RemoteDrawPrefill = {
  participants: RemoteParticipant[];
  exclusions: Record<RemoteParticipant['email'], RemoteParticipant['email'][]>;
};

export type RemoteDetailsGroup = {
  id: string;
  title: string;
  canConductDraw: boolean;
  draws: RemoteDraw[];
  previousYearsDrawPrefill: RemoteDrawPrefill | null;
};

export type RemoteDraw = { id: string; year: string };

export type RemoteDetailedDraw = {
  groupId: RemoteGroup['id'];
  title: string;
  id: string;
  year: string;
  allocations: RemoteAllocation[];
  allocation: RemoteAllocation | null;
  description: string;
};

export type RemoteAllocation = {
  id: string;
  from: string;
  to: string;
  fromIdeas: string[];
  toIdeas: string[];
  token: string;
  canProvideIdeas: boolean;
  hasMessagesToRecipient: boolean;
  hasMessagesFromSanta: boolean;
};

export type RemoteParticipant = {
  name: string;
  email: string;
};

export type RemoteEntry = {
  description: string;
  participants: RemoteParticipant[];
  exclusions: Record<RemoteParticipant['email'], RemoteParticipant['email'][] | undefined>;
};

export type AllocationResource = {
  from: {
    name: string;
    ideas: string[];
    access_token: string;
  };
  to: {
    name: string;
    ideas: string[];
  };
};

export type DrawResource = {
  title: string;
  description: string;
  year: number;
};

export type GroupResource = {
  title: string;
  previous_years_draw_prefill: RemoteDrawPrefill | null;
};

export type AllocationMessage = {
  id: string;
  message: string;
  fromMe: boolean;
  createdAt: string;
};

export type AllocationMessageResource = {
  id: string;
  message: string;
  is_from_me: boolean;
  created_at: string;
};

export type AllocationMessageDirection = 'to-recipient' | 'from-santa';

export type AllocationMessagesResource = {
  participant_name: string;
  conversation_type: AllocationMessageDirection;
  total: number;
};
