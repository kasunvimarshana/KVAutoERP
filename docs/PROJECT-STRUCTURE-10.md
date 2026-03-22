Modules/Tenant/
├── Domain/
│   ├── Entities/
│   │   ├── Tenant.php
│   │   └── TenantAttachment.php
│   ├── ValueObjects/
│   │   ├── DatabaseConfig.php
│   │   ├── MailConfig.php
│   │   ├── CacheConfig.php
│   │   ├── QueueConfig.php
│   │   ├── FeatureFlags.php
│   │   └── ApiKeys.php
│   ├── RepositoryInterfaces/
│   │   ├── TenantRepositoryInterface.php
│   │   └── TenantAttachmentRepositoryInterface.php
│   └── Events/
│       ├── TenantCreated.php
│       ├── TenantUpdated.php
│       ├── TenantConfigChanged.php
│       └── TenantDeleted.php
├── Application/
│   ├── DTOs/
│   │   ├── TenantData.php
│   │   ├── TenantConfigData.php
│   │   └── TenantAttachmentData.php
│   └── Services/
│       ├── CreateTenantService.php
│       ├── UpdateTenantService.php
│       ├── UpdateTenantConfigService.php
│       ├── DeleteTenantService.php
│       ├── UploadTenantAttachmentService.php
│       └── DeleteTenantAttachmentService.php
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   │   ├── Models/
│   │   │   │   ├── TenantModel.php
│   │   │   │   └── TenantAttachmentModel.php
│   │   │   └── Repositories/
│   │   │       ├── EloquentTenantRepository.php
│   │   │       └── EloquentTenantAttachmentRepository.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TenantController.php
│   │   │   └── TenantAttachmentController.php
│   │   ├── Requests/
│   │   │   ├── StoreTenantRequest.php
│   │   │   ├── UpdateTenantRequest.php
│   │   │   ├── UpdateTenantConfigRequest.php
│   │   │   └── UploadTenantAttachmentRequest.php
│   │   ├── Resources/
│   │   │   ├── TenantResource.php
│   │   │   ├── TenantCollection.php
│   │   │   ├── TenantConfigResource.php
│   │   │   └── TenantAttachmentResource.php
│   │   └── Middleware/
│   │       └── ResolveTenant.php (optional, if needed)
│   ├── Services/
│   │   └── FileStorageService.php (from Core)
│   └── Providers/
│       └── TenantServiceProvider.php
└── routes/
    └── api.php
