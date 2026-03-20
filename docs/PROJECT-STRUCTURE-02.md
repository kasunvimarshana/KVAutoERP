.

├── app/                                # PRESENTATION LAYER (Framework Delivery)

│   ├── Console/                        # CLI Entry Points

│   │   └── Commands/                   # Laravel Commands (delegates to Application Services)

│   ├── Http/                           # Web \& API Entry Points

│   │   ├── Controllers/                # Thin Controllers (Route Handling \& DTO handover)

│   │   ├── Middleware/                 # Request Filtering (Auth, Logging, Sanitization)

│   │   ├── Requests/                   # Form Requests (Validation \& mapping to DTOs)

│   │   └── Resources/                  # Eloquent Resources (API Transformers/JSON output)

│   └── Providers/                      # Dependency Injection (Binds Interfaces to Impl)

├── bootstrap/                          # Framework Bootstrapping logic

├── config/                             # Framework/Package Configuration files

├── database/                           # INFRASTRUCTURE (Persistence Schema)

│   ├── factories/                      # Domain Model factories for testing/seeding

│   ├── migrations/                     # Database Schema versioning

│   └── seeders/                        # Database Seeding/Initial state data

├── public/                             # Public entry (index.php) and compiled assets

├── resources/                          # UI Assets (Blade, Lang files, raw CSS/JS)

├── routes/                             # Route Definitions (api.php, web.php, console.php)

├── src/                                # THE CORE (Business Logic \& Technical Impl)

│   ├── Shared/                         # Cross-cutting concerns used by all contexts

│   │   ├── Domain/                     # Global Value Objects (e.g., Money, Email, UUID)

│   │   └── Infrastructure/             # Abstract Repositories or Base API Clients

│   └── \[BoundedContext]/               # e.g., "Ordering", "Identity", "Billing"

│       ├── Application/                # APPLICATION LAYER (Orchestration)

│       │   ├── Commands/               # Write-operation DTOs (e.g., CreateOrderRequest)

│       │   ├── Handlers/               # Command/Query Handlers (Execution logic)

│       │   ├── Queries/                # Read-operation DTOs (e.g., GetOrderDetails)

│       │   ├── DTOs/                   # Internal Data Transfer Objects (Data consistency)

│       │   └── Services/               # Application Services (Workflow \& Transaction mgmt)

│       ├── Domain/                     # DOMAIN LAYER (Business Rules/Pure Logic)

│       │   ├── Aggregates/             # Aggregate Roots (Consistency boundaries)

│       │   ├── Entities/               # Objects with Identity (Business logic methods)

│       │   ├── ValueObjects/           # Immutable objects (Price, Address, Status)

│       │   ├── Events/                 # Domain Events (e.g., OrderWasPaid)

│       │   ├── Exceptions/             # Business Rule Violations (Domain-specific)

│       │   ├── Factories/              # Logic for creating complex Domain objects

│       │   ├── Repositories/           # Repository Interfaces (Contracts/Abstractions)

│       │   ├── Services/               # Domain Services (Logic spanning multiple entities)

│       │   └── Policies/               # Business-level Authorization Rules

│       └── Infrastructure/             # INFRASTRUCTURE LAYER (Technical Implementation)

│           ├── Persistence/            # Repo Implementations (Eloquent/Redis/S3)

│           │   └── Eloquent/           # Eloquent Models \& Data Mappers

│           ├── ExternalApis/           # API Clients (Stripe, Twilio, SendGrid)

│           ├── Messaging/              # Event Listeners \& Queue Job implementations

│           └── Concerns/               # Infrastructure-specific Traits (e.g., LogsActivity)

├── tests/                              # TESTING SUITE

│   ├── Unit/                           # Testing pure Domain/Business logic (No DB access)

│   └── Feature/                        # Testing API Endpoints \& Application flows (With DB)

├── composer.json                       # PSR-4 mapping for "src/" Contexts

└── phpunit.xml                         # Test configuration and environment variables



