project-root/
├── app/
│   ├── Domain/                                     # Layer 1: The Heart (Framework Agnostic)
│   │   ├── Shared/                                 # Logic shared across all contexts
│   │   │   ├── ValueObjects/                       # e.g., Address, Money, Email
│   │   │   └── Events/                             # Global events (e.g., UserLoggedOut)
│   │   └── {ContextName}/                          # e.g., Billing, Inventory, Auth
│   │       ├── Aggregates/                         # Entry points to change state (e.g., Order)
│   │       ├── Entities/                           # Objects with Identity (e.g., Product)
│   │       ├── ValueObjects/                       # Immutable objects (e.g., SKU, Status)
│   │       ├── Events/                             # Specific Domain Events (e.g., OrderPlaced)
│   │       ├── Exceptions/                         # Business logic errors (e.g., InsufficientFunds)
│   │       ├── Repositories/                       # Interfaces only (Contracts)
│   │       ├── Services/                           # Multi-entity logic (e.g., TaxCalculator)
│   │       ├── Specifications/                     # Complex rule validation (e.g., IsEligibleForDiscount)
│   │       ├── Policies/                           # Domain-level permission logic
│   │       └── Enums/                              # Strict states (e.g., OrderStatus)
│   │
│   ├── Application/                                # Layer 2: The Orchestrator
│   │   └── {ContextName}/
│   │       ├── Commands/                           # Write-intent objects (e.g., CreateOrder)
│   │       ├── Handlers/                           # Executes Commands (calls Domain + Infra)
│   │       ├── Queries/                            # Read-intent objects (e.g., GetInvoiceDetails)
│   │       ├── DTOs/                               # Data Transfer between layers
│   │       ├── Listeners/                          # Reacts to Domain Events
│   │       └── Services/                           # Application-specific workflow logic
│   │
│   ├── Infrastructure/                             # Layer 3: The Implementation (Low-level)
│   │   ├── Persistence/
│   │   │   ├── Eloquent/                           # Eloquent Models & Repository Impls
│   │   │   └── Mappings/                           # Data transformers (Eloquent -> Domain)
│   │   ├── ExternalServices/                       # API Clients (Stripe, AWS, Mailchimp)
│   │   ├── Logging/                                # Custom loggers/monitoring
│   │   └── Bus/                                    # Command/Event Bus implementation
│   │
│   └── Web/                                        # Layer 4: Presentation (Entry Points)
│       ├── API/
│       │   └── v1/
│       │       ├── Controllers/                    # Slim controllers (calls App Layer)
│       │       ├── Requests/                       # Form validation (Input sanitation)
│       │       ├── Resources/                      # API Transformers (Output format)
│       │       └── Middleware/                     # Route-specific filters
│       ├── Console/                                # CLI Commands (Artisan)
│       └── Web/                                    # Traditional Blade/Inertia controllers
│
├── bootstrap/                                      # Framework booting logic
├── config/                                         # Application configuration files
├── database/                                       # Migration, Factories, and Seeders
├── public/                                         # Document root (index.php, assets)
├── resources/                                      # Frontend assets (Views, CSS, JS)
├── routes/                                         # Route definitions (api.php, web.php)
├── storage/                                        # Logs, compiled templates, file uploads
├── tests/
│   ├── Unit/                                       # Pure logic tests (Domain/Application)
│   ├── Feature/                                    # Integration/HTTP tests (Infra/Web)
│   └── Architecture/                               # ArchUnit/Pest tests (Ensures DDD boundaries)
└── vendor/                                         # Composer dependencies
