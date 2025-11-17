# AGENTS.md

This file provides guidance to AI coding agents when working with code in this repository.

## Project Structure & Setup

### Technology Stack

- **React** 18.2 with TypeScript
- **Build Tool**: Vite 4.4
- **Routing**: React Router v6
- **State Management**: Redux Toolkit 1.9
- **Styling**: Styled Components 6.1
- **API Client**: Ketting 7.5 (HAL/HATEOAS)
- **PWA**: Vite PWA Plugin with Workbox

### Development Environment

Docker-based workflow via Makefile:
```bash
make start      # Start Vite dev server (http://localhost:5173)
make install    # Install npm dependencies
make shell      # Interactive shell in dev container
make open       # Open app in browser
```

### Path Aliasing

TypeScript path alias configured:
- `@/` resolves to `./src/`
- Example: `import { Button } from '@/components/Button'`

### File Structure

```
client/app/
├── src/
│   ├── main.tsx              # App entry point
│   ├── env.ts                # Environment variables
│   ├── types.ts              # Shared TypeScript types
│   ├── theme.ts              # Styled Components theme
│   ├── store/                # Redux slices and API client
│   ├── components/           # Reusable UI components
│   ├── routes/               # Page components
│   ├── hooks/                # Custom React hooks
│   └── assets/               # SVG icons
├── public/                   # Static assets
├── vite.config.ts            # Vite configuration
├── pwa-assets.config.ts      # PWA icon generation
└── package.json
```

## React Component Patterns & Organization

### Styled Components Pattern

Components use styled-components with TypeScript:

**Example** (`src/components/Button.tsx`):
```tsx
import styled from 'styled-components';

interface ButtonProps {
  variant?: 'primary' | 'secondary';
  disabled?: boolean;
}

const InternalButton = styled.button<{ $variant?: string }>`
  background-color: ${({ theme, $variant }) =>
    $variant === 'secondary' ? theme.colors.secondary : theme.colors.primary
  };
  padding: ${({ theme }) => theme.spacing.md};

  &:disabled {
    opacity: 0.5;
  }
`;

export const Button = ({ variant, ...props }: ButtonProps) => {
  return <InternalButton $variant={variant} {...props} />;
};
```

### Key Patterns

1. **Transient Props**: Prefix with `$` (e.g., `$variant`) to prevent passing to DOM
2. **Theme Access**: Use `({ theme }) => theme.colors.background`
3. **Responsive Design**: Use `@media` queries and safe-area insets
4. **Functional Components**: Use inline type annotations, not `React.FC` (React 18 pattern)

### Component Organization

**Common Components** (`src/components/`):
- `Button.tsx` - Primary action button
- `InputField.tsx` - Text input field
- `TextField.tsx` - Multi-line text input
- `Layout.tsx` - Page layout with Outlet
- `Header.tsx` - Page header with navigation
- `List.tsx` - List with items and links
- `Loading.tsx` - Loading spinner
- `GiftIdeasEditor.tsx` - Edit gift ideas
- `GiftIdeasDisplay.tsx` - Display gift ideas
- `PWAInstallBanner.tsx` - Install prompt modal

### Layout Component

**Pattern** (`src/components/Layout.tsx`):
```tsx
import { Outlet } from 'react-router-dom';
import styled from 'styled-components';

const Container = styled.div`
  max-width: 600px;
  margin: 0 auto;
  padding: env(safe-area-inset-top) env(safe-area-inset-right)
           env(safe-area-inset-bottom) env(safe-area-inset-left);
`;

export const Layout = () => (
  <Container>
    <Outlet />
  </Container>
);
```

## Redux Toolkit Slice Creation Pattern

### Store Structure

**Location**: `src/store/`

```
store/
├── index.ts                    # Store configuration
├── hooks.ts                    # Typed useAppDispatch, useAppSelector
├── api.tsx                     # Ketting client instance
├── user/
│   ├── userSlice.ts           # Slice with reducers
│   ├── actions.ts             # Async thunks
│   ├── selectors.ts           # State selectors
│   └── index.ts               # Barrel export
├── localEntry/                # Local draw form state
├── localDraws/                # Local draw results
├── remoteGroups/              # Remote group management
├── remoteEntry/               # Remote draw form state
└── remoteDraws/               # Remote draw details
```

### Creating a New Slice

**1. Define Slice** (`userSlice.ts`):

```typescript
import { createSlice } from '@reduxjs/toolkit';
import { bootstrap, login, logout } from './actions';

interface UserState {
  status: 'idle' | 'loading' | 'authenticated' | 'unauthenticated';
  canLogin: boolean;
  canRegister: boolean;
  // ...
}

const initialState: UserState = {
  status: 'idle',
  canLogin: false,
  canRegister: false,
};

export const userSlice = createSlice({
  name: 'user',
  initialState,
  reducers: {
    // Synchronous actions
    reset: () => initialState,
  },
  extraReducers: (builder) => {
    builder
      .addCase(bootstrap.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(bootstrap.fulfilled, (state, action) => {
        state.status = action.payload.authenticated ? 'authenticated' : 'unauthenticated';
        state.canLogin = action.payload.canLogin;
        // ...
      });
  },
});

export const { reset } = userSlice.actions;
export default userSlice.reducer;
```

**2. Create Actions** (`actions.ts`):

```typescript
import { createAsyncThunk } from '@reduxjs/toolkit';
import { client, notifyAndThrowErrorMessage } from '@/store/api';

export const bootstrap = createAsyncThunk(
  'user/bootstrap',
  async () => {
    try {
      const state = await client.go().get();

      return {
        authenticated: state.links.has('account'),
        canLogin: state.links.has('login'),
        canRegister: state.links.has('register'),
        // ...
      };
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Unable to bootstrap');
    }
  }
);

export const login = createAsyncThunk(
  'user/login',
  async ({ email, password }: { email: string; password: string }, { dispatch }) => {
    try {
      await client.go().follow('login').post({ data: { email, password } });
      client.clearCache();

      // Chain thunks
      await dispatch(bootstrap());
    } catch (error) {
      await notifyAndThrowErrorMessage(error, 'Login failed');
    }
  }
);
```

**3. Create Selectors** (`selectors.ts`):

```typescript
import { RootState } from '@/store';

export const userSelector = (state: RootState) => state.user;
export const isAuthenticatedSelector = (state: RootState) =>
  state.user.status === 'authenticated';
```

**4. Export** (`index.ts`):

```typescript
export { default } from './userSlice';
export * from './selectors';
```

### Using in Components

```typescript
import { useAppSelector, useAppDispatch } from '@/store/hooks';
import { userSelector } from '@/store/user';
import { login } from '@/store/user/actions';

export const LoginPage = () => {
  const dispatch = useAppDispatch();
  const user = useAppSelector(userSelector);

  const handleLogin = async () => {
    await dispatch(login({ email, password }));
  };

  return <div>{user.status}</div>;
};
```

## Ketting HAL Client Usage Patterns

### Client Initialization

**Location**: `src/store/api.tsx`

```typescript
import Ketting from 'ketting';
import { BOOTSTRAP_URI } from '@/env';

export const client = new Ketting(BOOTSTRAP_URI);

// Middleware for access tokens
client.use((request, next) => {
  const token = new URLSearchParams(window.location.search).get('token');
  if (token) {
    request.headers.set('X-Access-Token', token);
  }
  return next(request);
});
```

### HAL Resource Navigation

**Common Patterns**:

```typescript
// Navigate to bootstrap endpoint
const state = await client.go().get();

// Check if link exists
if (state.links.has('login')) {
  // Link is available
}

// Follow a link
const resource = await client.go().follow('login');
await resource.post({ data: { email, password } });

// Follow all links (iterate collection)
for (const groupResource of state.followAll('items')) {
  const groupState = await groupResource.get();
  // Process group
}

// Navigate to URI directly
const resource = await client.go(uri).get();

// Delete resource
await resource.delete();

// Clear cache after mutations
client.clearCache();
```

### Resource Types

**Location**: `src/types.ts`

```typescript
import { State } from 'ketting';

export type GroupResource = {
  id: string;
  title: string;
  _links: {
    self: { href: string };
    'update-group'?: { href: string };
    'conduct-draw'?: { href: string };
  };
};

export type DrawResource = {
  year: number;
  description: string;
  // ...
};
```

### Error Handling

```typescript
export const notifyAndThrowErrorMessage = async (
  error: unknown,
  defaultMessage: string
) => {
  try {
    const response = await (error as any).response?.json();
    const message = response?.message || defaultMessage;
    toast.error(message);
    throw new Error(message);
  } catch {
    toast.error(defaultMessage);
    throw new Error(defaultMessage);
  }
};
```

### URI Encoding Pattern

```typescript
// Encode URI for storage
const id = btoa(resource.uri);

// Decode and restore resource
const resource = await client.go(atob(id)).get();
```

## How to Add New Routes

### Router Configuration

**Location**: `src/main.tsx`

Routes use React Router v6 with `createBrowserRouter`:

```typescript
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import { Layout } from '@/components/Layout';
import { Home } from '@/routes/home';

const router = createBrowserRouter([
  {
    path: '/',
    element: <Layout />,
    children: [
      {
        index: true,
        element: <Home />,
      },
      {
        path: 'login',
        element: <Login />,
      },
      {
        path: 'remote',
        children: [
          {
            index: true,
            element: <RemoteGroups />,
          },
          {
            path: ':id',
            element: <RemoteGroup />,
          },
        ],
      },
    ],
  },
]);
```

### Adding a New Route

**1. Create Route Component** (`src/routes/new-feature.tsx`):

```typescript
import { useNavigate, useParams } from 'react-router-dom';
import { Header } from '@/components/Header';
import { Content } from '@/components/Content';

export const NewFeature = () => {
  const navigate = useNavigate();
  const { id } = useParams();

  return (
    <>
      <Header>New Feature</Header>
      <Content>
        <p>Feature content</p>
      </Content>
    </>
  );
};
```

**2. Add to Router** (in `main.tsx`):

```typescript
{
  path: 'new-feature',
  element: <NewFeature />,
}

// With parameter
{
  path: 'new-feature/:id',
  element: <NewFeature />,
}
```

**3. Navigate Programmatically**:

```typescript
const navigate = useNavigate();
navigate('/new-feature');
navigate(`/new-feature/${id}`);
navigate(-1); // Go back
```

### Route Organization

- `/` - Home page
- `/login`, `/register` - Authentication
- `/remote/*` - Remote groups (requires authentication)
- `/local/*` - Local draws (client-side only)

## Styled Components Conventions

### Global Styles

**Location**: `src/components/GlobalStyles.ts`

```typescript
import { createGlobalStyle } from 'styled-components';

export const GlobalStyles = createGlobalStyle`
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: ${({ theme }) => theme.fonts.body};
    background-color: ${({ theme }) => theme.colors.background};
    color: ${({ theme }) => theme.colors.text};
  }
`;
```

### Theme Object

**Location**: `src/theme.ts`

```typescript
export const theme = {
  colors: {
    primary: '#CA052C',
    background: '#FFFFFF',
    text: '#000000',
    // ...
  },
  spacing: {
    xs: '4px',
    sm: '8px',
    md: '16px',
    lg: '24px',
    xl: '32px',
  },
  fonts: {
    body: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
  },
};
```

### Component Styling Best Practices

**Transient Props** (prevent DOM attribute pollution):
```typescript
const StyledButton = styled.button<{ $variant?: string }>`
  background: ${({ $variant }) => $variant === 'primary' ? 'red' : 'blue'};
`;

// Usage
<StyledButton $variant="primary">Click</StyledButton>
```

**Responsive Design**:
```typescript
const Container = styled.div`
  padding: 16px;

  @media (max-width: 768px) {
    padding: 8px;
  }

  @media (orientation: landscape) and (max-height: 500px) {
    padding: 4px;
  }
`;
```

**Safe Area Insets** (for notches):
```typescript
const SafeContainer = styled.div`
  padding-top: env(safe-area-inset-top);
  padding-right: env(safe-area-inset-right);
  padding-bottom: env(safe-area-inset-bottom);
  padding-left: env(safe-area-inset-left);
`;
```

**Animations**:
```typescript
import { keyframes } from 'styled-components';

const slideDown = keyframes`
  from { transform: translateY(-100%); }
  to { transform: translateY(0); }
`;

const AnimatedDiv = styled.div`
  animation: ${slideDown} 0.3s ease-out;
`;
```

## PWA Configuration & Asset Generation

### PWA Assets Config

**Location**: `pwa-assets.config.ts`

```typescript
import { defineConfig } from '@vite-pwa/assets-generator/config';

export default defineConfig({
  preset: {
    transparent: {
      sizes: [64, 192, 512],
      favicons: [[64, 'favicon.ico']],
    },
    maskable: {
      sizes: [512],
    },
    apple: {
      sizes: [180],
    },
  },
  images: ['public/app-icon.png'],
});
```

**Generate Assets**:
```bash
npm run generate-pwa-assets
```

### Vite PWA Plugin Configuration

**Location**: `vite.config.ts`

```typescript
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
  plugins: [
    VitePWA({
      registerType: 'autoUpdate',
      workbox: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg}'],
        globIgnores: ['screenshots/*', 'ios-splash-screens/*'],
        navigateFallbackDenylist: [/^\/api/, /^\/.well-known/],
      },
      manifest: {
        name: 'Secret Santa Draw',
        short_name: 'Secret Santa',
        display: 'standalone',
        theme_color: '#CA052C',
        background_color: '#CA052C',
        icons: [
          {
            src: 'pwa-64x64.png',
            sizes: '64x64',
            type: 'image/png',
          },
          {
            src: 'pwa-192x192.png',
            sizes: '192x192',
            type: 'image/png',
          },
          {
            src: 'pwa-512x512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'any',
          },
          {
            src: 'maskable-icon-512x512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'maskable',
          },
        ],
      },
    }),
  ],
});
```

### Service Worker Registration

**Location**: `src/main.tsx`

```typescript
import { registerSW } from 'virtual:pwa-register';

registerSW({ immediate: true });
```

### Install Banner

Custom PWA install prompt in `src/components/PWAInstallBanner.tsx`:
- Detects iOS devices for special instructions
- Rate-limits prompts (100-second cooldown)
- Animated modal UI
- Uses `beforeinstallprompt` event for non-iOS

## Vite Configuration Details

### Key Configuration

**Location**: `vite.config.ts`

```typescript
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import svgr from 'vite-plugin-svgr';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
  base: process.env.BASE_PUBLIC_PATH || '/',
  plugins: [
    react(),
    svgr(),
    VitePWA({ /* ... */ }),
  ],
  resolve: {
    alias: {
      '@': '/src',
    },
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://host.docker.internal:8000',
        changeOrigin: true,
        secure: false,
        ws: false,
      },
    },
  },
  build: {
    emptyOutDir: true,
  },
});
```

### Plugins

1. **@vitejs/plugin-react** - React Fast Refresh
2. **vite-plugin-svgr** - Import SVGs as React components
   ```typescript
   import Logo from '@/assets/logo.svg?react';
   <Logo />
   ```
3. **vite-plugin-pwa** - PWA support with Workbox

### Dev Server Proxy

API requests proxied to local backend:
- `/api/*` → `http://host.docker.internal:8000/api/*`
- Docker-specific host for Mac/Windows
- Change origin enabled for CORS

## Development Workflow with Vite

### Common Commands

```bash
make start      # Start dev server (http://localhost:5173)
make install    # Install npm dependencies
make lint       # Run ESLint and Stylelint
make format     # Fix code style violations (eslint --fix)
make shell      # Interactive shell in dev container
make open       # Open app in browser
make build      # Build for production
```

### Development Features

- **Fast Refresh**: Instant HMR for React components
- **TypeScript**: Strict mode enabled
- **ESLint**: Code quality enforcement
- **Stylelint**: CSS/styled-components linting
- **Prettier**: Code formatting via ESLint plugin

### Build Process

```bash
npm run build   # TypeScript compile + Vite build
# Outputs to client/app/dist/
```

## API Integration (Local vs Production)

### Environment Configuration

**Location**: `src/env.ts`

```typescript
export const BOOTSTRAP_URI = import.meta.env.VITE_BOOTSTRAP_URI || '/api/bootstrap';
```

### Local Development

- Vite proxy: `/api/*` → `http://host.docker.internal:8000`
- API must be running locally (see api/README.md)
- No CORS issues (proxy handles origin)

**Start both services**:
```bash
# Terminal 1 (API)
cd api && make start

# Terminal 2 (Client)
cd client && make start
```

### Production Deployment

- CloudFront routes `/api/*` to Lambda Function URL
- Static assets served from S3
- No proxy needed

### Access Token Pattern

```typescript
// Ketting middleware extracts token from URL
const token = new URLSearchParams(window.location.search).get('token');
if (token) {
  request.headers.set('X-Access-Token', token);
}
```

## Environment Variable Configuration

### Available Variables

**VITE_BOOTSTRAP_URI**:
- API bootstrap endpoint
- Default: `/api/bootstrap`
- Set via `.env.local` or build arguments

**BASE_PUBLIC_PATH**:
- Public path for assets
- Set by CDK during deployment
- Default: `/`

### Local Development

Create `.env.local` in `client/app/`:
```bash
VITE_BOOTSTRAP_URI=/api/bootstrap
```

### Vite Environment Variables

- Prefix: `VITE_` for client-side exposure
- Access: `import.meta.env.VITE_*`
- Type: String (no boolean/number parsing)

## CDK Deployment Specifics

### Infrastructure

**Location**: `cdk/lib/stack.ts`

**Components**:
1. **S3 Bucket** - Private storage for static assets
2. **CloudFront Distribution** - CDN with custom domain
3. **Asset Deployments** - Three strategies:
   - Cached (js, css) - 365-day TTL
   - Uncached (index.html, sw.js) - No cache
   - Well-known (.well-known/*) - JSON content-type

### CloudFront Behaviors

**Default Behavior** (S3 origin):
- Cache policy: `CACHING_OPTIMIZED`
- Error: 404 → index.html (SPA routing)
- HTTPS redirect

**/api/\* Behavior** (API origin):
- Cache policy: `CACHING_DISABLED`
- All HTTP methods allowed
- Proxies to configured API domain

### Deployment Process

```bash
# 1. Configure
cp cdk/lib/config.ts.example cdk/lib/config.ts
# Edit config.ts:
# - domainName
# - certificateArn
# - apiOriginDomainName

# 2. Set credentials
export AWS_ACCESS_KEY_ID="..."
export AWS_SECRET_ACCESS_KEY="..."

# 3. Deploy
make deploy
```

### Build Artifacts

**Output**: `client/app/dist/`

**Files**:
- `index.html` - No-cache headers
- `*.js`, `*.css` - 365-day cache
- `sw.js` - Service worker, no-cache
- `.well-known/*` - JSON content-type
- PWA icons (pwa-*.png)

## React/TypeScript Conventions

### Import Organization

Via `eslint-plugin-simple-import-sort`:
```typescript
// 1. React/third-party
import React from 'react';
import styled from 'styled-components';

// 2. Absolute path imports
import { Button } from '@/components/Button';
import { userSelector } from '@/store/user';

// 3. Relative imports
import { helper } from './helper';
```

### Type Safety

- Strict TypeScript mode enabled
- No unused locals/parameters
- No fallthrough cases in switch
- All imports typed

### File Naming

- Components: PascalCase (`Button.tsx`)
- Utilities: camelCase (`allocator.ts`)
- Routes: kebab-case (`conduct-draw.tsx`)
- Redux: camelCase with suffix (`userSlice.ts`)

### Async/Error Handling

```typescript
// Redux thunk pattern
const result = await dispatch(login({ email, password }));
unwrapResult(result); // Throws on error

// Toast notifications
import { toast } from 'react-toastify';
toast.success('Success!');
toast.error('Error occurred');
```

### Component Prop Patterns

```typescript
import { PropsWithChildren } from 'react';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary';
}

export const Button = ({
  variant = 'primary',
  children,
  ...props
}: PropsWithChildren<ButtonProps>) => {
  return <StyledButton $variant={variant} {...props}>{children}</StyledButton>;
};
```

### State Management Strategy

- **Redux**: Global UI state (auth, remote data)
- **React Router**: Page state (navigation)
- **localStorage**: Persistent local draws
- **Component State**: Local UI state (forms, toggles)

## LocalStorage Schema

### Local Draws Storage

**Key**: `secret-santa-local-draws`

**Structure**:
```typescript
{
  draws: Array<{
    id: string;                    // UUID for draw
    at: number;                    // Unix timestamp
    entry: {
      title: string;               // Draw title
      participants: string[];      // Array of participant names
      exclusions: Record<string, string[] | undefined>;  // Map of exclusions
    };
    allocation: Record<string, string>;  // Map of giver -> recipient
  }>
}
```

**Example**:
```json
{
  "draws": [
    {
      "id": "abc-123",
      "at": 1701388800000,
      "entry": {
        "title": "Office Secret Santa 2024",
        "participants": ["Alice", "Bob", "Charlie"],
        "exclusions": {
          "Alice": ["Bob"]
        }
      },
      "allocation": {
        "Alice": "Charlie",
        "Bob": "Alice",
        "Charlie": "Bob"
      }
    }
  ]
}
```

**Persistence**: Redux store automatically syncs to localStorage on state changes (see `src/store/index.ts`)

**Clearing**: Use Redux `removeDraw` action or clear localStorage directly
