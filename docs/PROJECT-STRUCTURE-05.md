app/
  Domain/
    Tenant/
      Entities/
        Tenant.php                 # Domain entity with business logic
      ValueObjects/
        DatabaseConfig.php          # Value object for DB config
        MailConfig.php              # Value object for mail config
        FeatureFlags.php            # Value object for feature flags
      RepositoryInterfaces/
        TenantRepositoryInterface.php
      Events/
        TenantCreated.php
        TenantUpdated.php
        TenantConfigChanged.php
  Application/
    Tenant/
      UseCases/
        CreateTenant.php
        UpdateTenant.php
        UpdateTenantConfig.php
        DeleteTenant.php
        GetTenant.php
        ListTenants.php
      DTOs/
        TenantData.php
        TenantConfigData.php
  Infrastructure/
    Persistence/
      Eloquent/
        Models/
          TenantModel.php            # Eloquent model (implements Tenant entity)
        Repositories/
          EloquentTenantRepository.php
    Http/
      Controllers/
        TenantController.php
      Requests/
        StoreTenantRequest.php
        UpdateTenantRequest.php
        UpdateTenantConfigRequest.php
      Resources/
        TenantResource.php
        TenantConfigResource.php
    Messaging/                      # (Optional) Event publishers for config changes
      TenantEventProducer.php
  Console/                          # Commands for seeding, etc.
database/
  migrations/
    xxx_create_tenants_table.php
routes/
  api.php
