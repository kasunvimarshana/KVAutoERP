core/
├── src/
│   ├── Domain/
│   │   ├── Contracts/
│   │   │   └── Repositories/
│   │   │       └── RepositoryInterface.php
│   │   ├── Events/
│   │   │   └── BaseEvent.php
│   │   ├── ValueObjects/
│   │   │   └── (none – placeholders for value objects)
│   │   └── Exceptions/
│   │       └── DomainException.php
│   │
│   ├── Application/
│   │   ├── DTOs/
│   │   │   └── BaseDto.php
│   │   └── Services/
│   │       └── FileStorageServiceInterface.php
│   │
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   │   ├── Eloquent/
│   │   │   │   ├── Models/
│   │   │   │   │   └── BaseModel.php
│   │   │   │   └── Traits/
│   │   │   │       ├── HasUuid.php
│   │   │   │       └── HasTenant.php
│   │   │   └── Repositories/
│   │   │       ├── BaseRepository.php
│   │   │       ├── EloquentRepository.php
│   │   │       ├── ApiRepository.php
│   │   │       └── CollectionRepository.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── BaseController.php
│   │   │   ├── Middleware/
│   │   │   │   └── ResolveTenant.php
│   │   │   └── Resources/
│   │   │       └── BaseResource.php
│   │   ├── Services/
│   │   │   └── FileStorageService.php
│   │   └── Providers/
│   │       └── CoreServiceProvider.php
│   │
│   └── Shared/
│       ├── Helpers/
│       │   └── helpers.php
│       └── Exceptions/
│           └── BaseException.php
├── config/
│   └── core.php
├── database/
│   └── migrations/
├── tests/
├── composer.json
└── README.md
