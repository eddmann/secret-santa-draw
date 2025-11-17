# AGENTS.md

This file provides guidance to AI coding agents when working with code in this repository.

## Quick Start & Development Workflow

### Starting the Development Environment

```bash
make start      # Start Docker containers (PHP 8.4, PostgreSQL, Redis)
make restart    # Restart containers
make stop       # Stop and clean up
```

The API runs at `http://localhost:8000` using Docker Compose.

### Common Development Commands

```bash
make composer   # Install/update Composer dependencies
make test       # Run Pest test suite
make lint       # Run Laravel Pint linting
make format     # Fix code style violations
make can-release # Run all CI checks (security, tests, linting)
make security   # Check for known vulnerabilities
make shell      # Access container shell
make logs       # Tail container logs
make open       # Open API in browser
```

### Running Individual Tests

```bash
# From api/app/ directory
./vendor/bin/pest                                    # Run all tests
./vendor/bin/pest tests/Feature/GroupTest.php       # Run specific file
./vendor/bin/pest --filter=test_can_create_group    # Run specific test
```

## Database Operations & Migrations

### Running Migrations

```bash
# Inside container (make shell)
php artisan migrate              # Run pending migrations
php artisan migrate:refresh      # Drop all tables and re-run
php artisan migrate:refresh --seed # Re-migrate and seed data
```

### Database Seeding

Two seeders provide test data:
- `DatabaseSeeder` - Runs all seeders
- `OfficeGroupSeeder` - Creates office-themed test groups with diverse gift ideas
- `FamilyGroupSeeder` - Creates family-themed test groups

```bash
php artisan db:seed
php artisan db:seed --class=OfficeGroupSeeder
```

### Database Schema

**Core Tables**:
- `users` - User accounts (id, name, email, password)
- `groups` - Draw groups (id, owner_id, title)
- `draws` - Annual draws (id, group_id, year, description)
  - Unique constraint: `[group_id, year]`
- `allocations` - Participant assignments (id, draw_id, from_*, to_*, ideas, access_token)
  - Unique constraint: `[draw_id, from_email, to_email]`
  - Cascade delete on draw_id
- `jobs` - Queue job tracking
- `sessions` - Session storage

**Migration Files**: `api/app/database/migrations/`

## Model & Relationship Patterns

### Core Models

**User** (`app/Models/User.php`):
```php
hasMany groups (as owner)
hasMany allocations (as from_user_id)
hasMany allocations (as to_user_id)
```

**Group** (`app/Models/Group.php`):
```php
belongsTo owner (User)
hasMany draws

// Business logic methods
isOwner(?User $user): bool
canConductDraw(?int $userId): bool
draw(year, description, participants): Draw  // Main allocation logic
allocate(participants, attempts): array      // Recursive shuffle algorithm
```

**Draw** (`app/Models/Draw.php`):
```php
belongsTo group
hasMany allocations (cascade delete)

// Access methods
allocationFor(?int $userId, ?string $accessToken): ?Allocation
```

**Allocation** (`app/Models/Allocation.php`):
```php
belongsTo draw

// Casts
'from_ideas' => 'array'  // JSON storage

// Computed attributes
to_name: Attribute       // Fetches recipient's name
to_ideas: Attribute      // Fetches recipient's ideas

// Access control
canAccess(?int $userId, ?string $accessToken): bool
```

### Secret Santa Allocation Algorithm

Located in `Group::allocate()` (app/Models/Group.php):
- Recursive shuffle with constraint satisfaction
- Prevents self-assignment
- Respects exclusion rules (couples, previous pairings)
- Retries up to 100 times to find valid allocation
- Throws exception if constraints make allocation impossible

## Adding New API Endpoints

### Pattern: Controller → Resource → Routes

**1. Create Controller** (`app/Http/Controllers/`):

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExampleController extends Controller
{
    public function index(Request $request)
    {
        // Authorization check
        if (!$this->canAccess($request->user())) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        // Fetch data
        $items = Example::all();

        // Return HAL resource
        return new ExampleCollection($items);
    }

    public function store(Request $request)
    {
        // Validate
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'nested.*.field' => 'required|string',
        ]);

        // Create
        $item = Example::create($validated);

        // Return 201 CREATED
        return (new ExampleResource($item))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
```

**2. Create HAL Resource** (`app/Http/Resources/`):

See "HAL Resource Creation" section below.

**3. Register Routes** (`routes/web.php`):

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/examples', [ExampleController::class, 'index'])->name('examples.index');
    Route::post('/api/examples', [ExampleController::class, 'store'])->name('examples.store');
});
```

### HTTP Status Codes (Symfony Response Constants)

```php
use Symfony\Component\HttpFoundation\Response;

Response::HTTP_OK                    // 200
Response::HTTP_CREATED               // 201
Response::HTTP_ACCEPTED              // 202
Response::HTTP_FORBIDDEN             // 403
Response::HTTP_NOT_FOUND             // 404
Response::HTTP_UNPROCESSABLE_ENTITY  // 422
```

## HAL Resource Creation & JSON Hypermedia

### HAL Format Overview

Every API response follows HAL (Hypertext Application Language):

```json
{
  "_links": {
    "self": { "href": "/api/resource/1" },
    "action": { "href": "/api/resource/1/action" }
  },
  "_embedded": {
    "items": [...]
  },
  "data": { ... }
}
```

### Resource Pattern

**Simple Resource** (`app/Http/Resources/GroupResource.php`):

```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            '_links' => array_filter([
                'self' => [
                    'href' => route('groups.show', $this->id),
                ],
                'update-group' => $this->isOwner($request->user()) ? [
                    'href' => route('groups.update', $this->id),
                ] : null,
                'conduct-draw' => $this->canConductDraw($request->user()?->id) ? [
                    'href' => route('groups.draws.store', $this->id),
                ] : null,
            ]),
            '_embedded' => [
                'draws' => new DrawCollection($this->draws),
            ],
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
```

**Collection Resource** (`app/Http/Resources/GroupCollection.php`):

```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GroupCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            '_links' => array_filter([
                'self' => [
                    'href' => route('groups.index'),
                ],
                'add-group' => [
                    'href' => route('groups.store'),
                ],
                'items' => $this->collection->map(fn ($group) => [
                    'href' => route('groups.show', $group->id),
                ])->toArray(),
            ]),
            '_embedded' => [
                'groups' => $this->collection,
            ],
        ];
    }
}
```

### Key Patterns

1. **Conditional Links**: Use `array_filter()` to remove null links based on permissions
2. **Self-referencing**: Always include `self` link with `route()` helper
3. **Embedded Resources**: Nest related resources in `_embedded` for single-request completeness
4. **Named Routes**: Use named routes for maintainability
5. **No Wrapping**: `JsonResource::withoutWrapping()` called in `AppServiceProvider`

## Event & Listener Setup (Queue Jobs)

### Event-Driven Architecture

**1. Create Event** (`app/Events/DrawConducted.php`):

```php
namespace App\Events;

use App\Models\Draw;
use Illuminate\Foundation\Events\Dispatchable;

class DrawConducted
{
    use Dispatchable;

    public function __construct(public Draw $draw) {}
}
```

**2. Create Listener** (`app/Listeners/SendDrawConductedNotification.php`):

```php
namespace App\Listeners;

use App\Events\DrawConducted;
use App\Mail\DrawConducted as DrawConductedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDrawConductedNotification implements ShouldQueue
{
    public function handle(DrawConducted $event): void
    {
        foreach ($event->draw->allocations as $allocation) {
            Mail::to($allocation->from_email)
                ->queue(new DrawConductedMail($allocation));
        }
    }
}
```

**3. Create Mailable** (`app/Mail/DrawConducted.php`):

```php
namespace App\Mail;

use App\Models\Allocation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class DrawConducted extends Mailable
{
    use Queueable;

    public function __construct(public Allocation $allocation) {}

    public function envelope()
    {
        return new Envelope(
            subject: 'Secret Santa Draw - Your Allocation',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.draw-conducted',
        );
    }
}
```

**4. Dispatch Event** (in controller):

```php
use App\Events\DrawConducted;

event(new DrawConducted($draw));
```

### Queue Configuration

- Development: `QUEUE_CONNECTION=sync` (synchronous)
- Production: `QUEUE_CONNECTION=sqs` (AWS SQS)
- Jobs table: Tracks queued jobs
- Failed jobs table: Tracks failures for replay

### Running Queue Worker Locally

To test async queue jobs in development, change `QUEUE_CONNECTION` to `database` and run:

```bash
# Inside container (make shell)
php artisan queue:work

# Or run specific queue
php artisan queue:work --queue=default

# Process one job and stop
php artisan queue:work --once

# Clear failed jobs
php artisan queue:flush
```

**When to use**:
- Testing email notifications from `DrawConducted` event
- Debugging queue job failures
- Verifying job serialization works correctly

**Note**: With `QUEUE_CONNECTION=sync`, jobs run immediately without needing the worker.

## Testing with Pest

### Test Structure

- `tests/Feature/` - HTTP endpoint tests
- `tests/Unit/` - Business logic tests
- `tests/Pest.php` - Configuration
- `tests/TestCase.php` - Base class with helpers

### Feature Test Pattern

**Example** (`tests/Feature/GroupTest.php`):

```php
use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create group', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/groups', [
            'title' => 'Test Group',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('_links.self.href', route('groups.show', Group::first()->id))
        ->assertJsonPath('title', 'Test Group');
});

test('unauthenticated user cannot create group', function () {
    $response = $this->postJson('/api/groups', [
        'title' => 'Test Group',
    ]);

    $response->assertStatus(401);
});
```

### Testing Event Dispatch

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\DrawConducted;

test('conducting draw sends emails to all participants', function () {
    Mail::fake();

    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)
        ->postJson(route('groups.draws.store', $group->id), [
            'year' => 2024,
            'description' => 'Test Draw',
            'participants' => $this->participants(3),
        ]);

    $response->assertStatus(201);

    Mail::assertQueued(DrawConducted::class, 3);
});
```

### TestCase Helper Methods

**participants() helper** (`tests/TestCase.php`):

```php
protected function participants(int $count): array
{
    return array_map(fn ($i) => [
        'name' => "Participant {$i}",
        'email' => "participant{$i}@example.com",
    ], range(1, $count));
}
```

### HAL Response Assertions

```php
$response->assertJsonPath('_links.self.href', $expectedUrl)
    ->assertJsonPath('_embedded.draws.0.year', 2024);
```

## Authentication & Authorization

### Sanctum Configuration

- Default guard: `web` (session-based, not API tokens)
- Middleware: `auth:sanctum`
- Session driver: `database`
- Session lifetime: 120 minutes

### Dual Access Model

1. **Authenticated Users**: Via session cookies
2. **Access Tokens**: Via `X-Access-Token` header (for allocations)

**Controller Pattern**:

```php
public function show(Request $request, Group $group, Draw $draw)
{
    $allocation = $draw->allocationFor(
        $request->user()?->id,
        $request->header('X-Access-Token')
    );

    if (!$allocation) {
        return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
    }

    return new DrawResource($draw);
}
```

### Testing Authentication

```php
$user = User::factory()->create();

$this->actingAs($user)
    ->getJson('/api/groups')
    ->assertStatus(200);
```

## Environment Configuration

### Development Environment

**Docker dev.env** (`docker/dev.env.example`):
```bash
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=secret_santa
DB_USERNAME=secret_santa
DB_PASSWORD=secret_santa

QUEUE_CONNECTION=sync
MAIL_MAILER=log

APP_ENV=local
APP_DEBUG=true
```

### Production Environment

**CDK environment.ts** (`cdk/lib/environment.ts.example`):
```typescript
APP_ENV: 'production',
APP_DEBUG: 'false',
QUEUE_CONNECTION: 'sqs',
SQS_QUEUE: workerQueue.queueUrl,
MAIL_MAILER: 'mailgun',
MAILGUN_DOMAIN: 'your-domain.com',
MAILGUN_SECRET: 'key-...',
SESSION_DRIVER: 'database',
```

### Required Environment Variables

- `APP_KEY` - Laravel encryption key (generate with `php artisan key:generate`)
- `APP_URL` - Application URL
- `DB_*` - Database credentials
- `QUEUE_CONNECTION` - Queue driver (sync/sqs)
- `MAIL_MAILER` - Mail driver (log/mailgun)
- `MAILGUN_*` - Mailgun credentials (production)

## Docker Workflow

### Docker Compose Structure

**Services** (`docker/compose.yaml`):
- `app` - PHP 8.4 with Composer
- `db` - PostgreSQL 16

### Volume Management

- `./app:/var/www/html` - Code mounted for hot reload
- `postgres-data` - Database persistence

### Container Access

```bash
make shell          # Interactive shell in app container
make logs           # Tail all container logs
make ps             # List running containers
```

### Database Access

```bash
# From host
docker exec -it secret-santa-api-db psql -U secret_santa -d secret_santa

# From app container
psql -h db -U secret_santa -d secret_santa
```

## CDK Deployment

### Infrastructure Components

**Lambda Functions** (`cdk/lib/stack.ts`):
1. **WebFunction** (PhpFpmFunction) - Handles HTTP requests
   - Handler: `public/index.php`
   - Timeout: 28 seconds
   - Function URL: Public access
2. **ConsoleFunction** - Runs artisan commands
   - Handler: `artisan`
   - Timeout: 2 minutes
3. **WorkerFunction** - Processes queue jobs
   - Handler: `Bref\LaravelBridge\Queue\QueueHandler`
   - Timeout: 1 minute
   - Event source: SQS

**SQS Queues**:
- WorkerQueue - Main job queue (6-minute visibility timeout)
- Dead Letter Queue - Failed jobs (5 max receives, 14-day retention)

### Deployment Process

```bash
# 1. Configure environment
cp cdk/lib/environment.ts.example cdk/lib/environment.ts
# Edit environment.ts with actual values

# 2. Set AWS credentials
export AWS_ACCESS_KEY_ID="..."
export AWS_SECRET_ACCESS_KEY="..."

# 3. Deploy
make deploy
```

### PHP Version

- Runtime: Bref PHP 8.4
- Specified in stack: `Runtime.PROVIDED_AL2023`
- Layer: `bref-php-84`

### Region

- Deployment region: `eu-west-1` (Dublin)

## Laravel Conventions & Codebase Patterns

### Distinctive Patterns

1. **HAL/HATEOAS-First API** - Every response navigable via links
2. **Access Token Pattern** - Unauthenticated access via tokens
3. **Event-Driven Async** - Email notifications via queued listeners
4. **Model-Driven Authorization** - Permission checks in models
5. **Recursive Allocation** - Constraint satisfaction algorithm with retries

### AppServiceProvider Configuration

**No JSON Wrapping** (`app/Providers/AppServiceProvider.php`):
```php
public function boot()
{
    JsonResource::withoutWrapping();
}
```

### Route Naming

All routes use named routes for href generation:
```php
Route::get('/api/groups', [GroupController::class, 'index'])->name('groups.index');
route('groups.index') // Returns: /api/groups
```

### Validation Patterns

**Nested Arrays**:
```php
$request->validate([
    'participants' => 'required|array|min:3',
    'participants.*.name' => 'required|string',
    'participants.*.email' => 'required|email|distinct',
    'exclusions' => 'array',
    'exclusions.*.from' => 'required|email',
    'exclusions.*.to' => 'required|email|different:exclusions.*.from',
]);
```

### Dependencies

**Key Composer Packages** (`composer.json`):
- `laravel/framework` ^11.9
- `laravel/sanctum` ^4.0
- `bref/bref` ^2.3
- `bref/laravel-bridge` ^2.4
- `pestphp/pest` ^3.5 (dev)
- `laravel/pint` ^1.13 (dev)
