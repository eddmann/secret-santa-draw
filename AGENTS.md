# AGENTS.md

This file provides guidance to AI coding agents when working with code in this repository.

## Project Overview

Secret Santa Draw is a monorepo containing three sub-projects:
- **API**: Laravel 11 RESTful HAL-based API (PHP 8.4, PostgreSQL)
- **Client**: React PWA with TypeScript and Vite
- **iOS**: Native iOS app wrapping the PWA with Universal Link support

The platform enables both local offline Secret Santa draws and remote online draws with account management, email notifications, and gift idea sharing.

## Monorepo Structure

Each sub-project has its own README.md and development workflow:
- `/api/` - Backend API and CDK infrastructure
- `/client/` - React client app and CDK infrastructure
- `/ios/` - iOS native wrapper application

## Sub-Project Documentation

For detailed information about working within each sub-project, see:

- **[api/AGENTS.md](api/AGENTS.md)** - Laravel/PHP API development details
  - Database operations and migrations
  - HAL resource creation patterns
  - Testing with Pest
  - Event/listener setup for queue jobs
  - Docker workflow
  - CDK deployment specifics

- **[client/AGENTS.md](client/AGENTS.md)** - React/TypeScript client development details
  - Redux Toolkit slice patterns
  - Ketting HAL client usage
  - React component and styling conventions
  - PWA configuration
  - Vite configuration and workflow
  - CDK deployment specifics

- **[ios/AGENTS.md](ios/AGENTS.md)** - iOS native app development details
  - Xcode project structure
  - WebView configuration and customization
  - Universal Link and deep link handling
  - Building and debugging
  - Platform detection features

## Common Commands

### API (`/api/`)
```bash
make start          # Start development environment (Docker Compose)
make restart        # Restart development environment
make stop           # Stop and clean up
make composer       # Install/update Composer dependencies
make test           # Run Pest test suite
make lint           # Run Pint linting
make format         # Fix code style violations
make can-release    # Run all CI checks
make open           # Open API in browser
make shell          # Access container shell
make build          # Build for deployment
make deploy         # Deploy via CDK to AWS
```

### Client (`/client/`)
```bash
make start          # Start Vite dev server
make install        # Install NPM dependencies
make lint           # Run ESLint and Stylelint
make format         # Fix code style violations
make open           # Open client in browser
make shell          # Access dev environment shell
make build          # Build for production
make deploy         # Deploy via CDK to AWS
```

### Running Single Tests (API)
```bash
cd api/app
./vendor/bin/pest tests/Feature/SomeTest.php
./vendor/bin/pest --filter=test_name
```

## Architecture Overview

### API Architecture

Laravel 11 RESTful API using HAL/HATEOAS for hypermedia navigation. Features session-based authentication with Sanctum, dual access model (authenticated users + access tokens), event-driven async email notifications, and a constraint-satisfaction algorithm for Secret Santa allocation. Deployed as serverless Lambda functions with Bref PHP 8.4 runtime.

**See [api/AGENTS.md](api/AGENTS.md) for detailed documentation.**

### Client Architecture

React 18 PWA with Redux Toolkit state management and Ketting HAL client for API integration. Supports both local offline draws (localStorage) and remote online draws (authenticated). Built with Vite, styled-components, and deployed via CloudFront + S3 with service worker caching.

**See [client/AGENTS.md](client/AGENTS.md) for detailed documentation.**

### iOS Architecture

Native iOS wrapper around the PWA using WKWebView. Provides Universal Link support for deep linking, platform cookie injection for server-side detection, and native features like SafariViewController for external links. Based on ios-pwa-wrap template.

**See [ios/AGENTS.md](ios/AGENTS.md) for detailed documentation.**

### Infrastructure

**API**: Serverless Lambda (Bref PHP 8.4) + SQS + RDS Aurora PostgreSQL
**Client**: CloudFront + S3 with `/api/*` proxy to Lambda Function URL
**Region**: `eu-west-1` (Dublin)
**Deployment**: CDK-based (`make deploy` from respective project)

**See sub-project AGENTS.md files for CDK stack details.**

## Development Workflow

### API Development
- Makefile-based workflow manages Docker containers (PHP, PostgreSQL, Redis)
- Tests use Pest framework with Laravel plugin
- Code style: Laravel Pint (PSR-12 based)
- Database seeding available: `OfficeGroupSeeder` and `FamilyGroupSeeder` provide test data
- Logs suppressed during test runs

### Client Development
- Vite dev server with HMR
- ESLint + Stylelint for code quality
- Prettier for formatting
- TypeScript strict mode
- CSS-in-JS with styled-components

### iOS Development
- Xcode project at `/ios/Secret-Santa-Draw.xcodeproj`
- Update `Settings.swift` to change root URL or platform cookie
- Universal Links configured via associated domains

## Key Architectural Patterns

1. **HAL/HATEOAS-First API**: Client discovers capabilities via links, no URL coupling
2. **Event-Driven Async Jobs**: Email notifications processed asynchronously via queues
3. **Dual Access Model**: Authenticated users + access tokens for non-registered participants
4. **Constraint-Satisfaction Algorithm**: Recursive shuffle for Secret Santa allocation
5. **Serverless Deployment**: Lambda + SQS for auto-scaling, pay-per-use
6. **Hybrid Local/Remote**: PWA supports offline local draws + online remote draws
7. **Progressive Enhancement**: PWA works offline, native iOS app adds deep linking

## Testing Philosophy

- API: Feature tests for endpoints, unit tests for business logic
- Client: No test suite currently (RAD development approach)
- CI checks: `make can-release` runs security, tests, and linting

## Deployment Architecture

**Production URL**: `https://secret-santa.eddmann.com`

**Flow**:
1. Client requests hit CloudFront distribution
2. Static assets served from S3 with long cache TTL
3. `/api/*` requests proxied to Lambda Function URL
4. API responses include HAL links for navigation
5. Email notifications queued to SQS, processed by WorkerFunction
6. iOS app deep links route to PWA via Universal Links

## Notes

- Built with RAD approach (couple of evenings development time)
- Focus on shipping over perfection
- Part of annual tradition (2020-2024) of over-engineering Secret Santa allocation
- API uses database sessions (not Redis) for simplicity
- Local draws stored in browser localStorage (no backend persistence)
