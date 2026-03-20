<?php

namespace LaravelDddModules\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;
use LaravelDddModules\Tests\TestCase;

class ModuleGeneratorTest extends TestCase
{
    private ModuleGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $files = new Filesystem();
        $this->generator = new ModuleGenerator($files, new StubCompiler($files));
    }

    public function test_build_replacements_contains_all_keys(): void
    {
        $replacements = $this->generator->buildReplacements('Order', 'App\\Modules\\Order');

        $this->assertArrayHasKey('{{ module }}', $replacements);
        $this->assertArrayHasKey('{{ moduleLower }}', $replacements);
        $this->assertArrayHasKey('{{ moduleSnake }}', $replacements);
        $this->assertArrayHasKey('{{ moduleKebab }}', $replacements);
        $this->assertArrayHasKey('{{ moduleCamel }}', $replacements);
        $this->assertArrayHasKey('{{ modulePlural }}', $replacements);
        $this->assertArrayHasKey('{{ moduleSnakePlural }}', $replacements);
        $this->assertArrayHasKey('{{ namespace }}', $replacements);
        $this->assertArrayHasKey('{{ domainNamespace }}', $replacements);
        $this->assertArrayHasKey('{{ appNamespace }}', $replacements);
        $this->assertArrayHasKey('{{ infraNamespace }}', $replacements);
        $this->assertArrayHasKey('{{ presNamespace }}', $replacements);
    }

    public function test_build_replacements_values_are_correct(): void
    {
        $replacements = $this->generator->buildReplacements('UserProfile', 'App\\Modules\\UserProfile');

        $this->assertSame('UserProfile', $replacements['{{ module }}']);
        $this->assertSame('userprofile', $replacements['{{ moduleLower }}']);
        $this->assertSame('user_profile', $replacements['{{ moduleSnake }}']);
        $this->assertSame('user-profile', $replacements['{{ moduleKebab }}']);
        $this->assertSame('userProfile', $replacements['{{ moduleCamel }}']);
        $this->assertSame('App\\Modules\\UserProfile', $replacements['{{ namespace }}']);
        $this->assertSame('App\\Modules\\UserProfile\\Domain', $replacements['{{ domainNamespace }}']);
    }

    public function test_get_stub_file_map_returns_expected_keys(): void
    {
        $map = $this->generator->getStubFileMap('Order', '/tmp/Modules/Order', 'App\\Modules\\Order');

        $expectedKeys = [
            'provider', 'entity', 'value_object', 'aggregate',
            'repository_interface', 'domain_service', 'domain_event',
            'use_case', 'dto', 'eloquent_model', 'eloquent_repository',
            'api_controller', 'web_controller', 'form_request',
            'api_resource', 'api_routes', 'web_routes',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $map, "Missing key: {$key}");
        }
    }

    public function test_get_stub_file_map_contains_stub_and_target(): void
    {
        $map = $this->generator->getStubFileMap('Order', '/tmp/Modules/Order', 'App\\Modules\\Order');

        foreach ($map as $key => $info) {
            $this->assertArrayHasKey('stub', $info, "Missing 'stub' key for: {$key}");
            $this->assertArrayHasKey('target', $info, "Missing 'target' key for: {$key}");
        }
    }

    public function test_generate_creates_directory_structure(): void
    {
        $result = $this->generator->generate('Order');

        $this->assertSame('Order', $result['module']);
        $this->assertNotEmpty($result['path']);
        $this->assertNotEmpty($result['namespace']);
        $this->assertIsArray($result['directories']);
        $this->assertIsArray($result['files']);
        $this->assertNotEmpty($result['directories']);
    }

    public function test_generate_creates_domain_directories(): void
    {
        $result = $this->generator->generate('Invoice');
        $modulePath = $result['path'];

        $this->assertDirectoryExists("{$modulePath}/Domain/Entities");
        $this->assertDirectoryExists("{$modulePath}/Domain/ValueObjects");
        $this->assertDirectoryExists("{$modulePath}/Domain/Repositories");
        $this->assertDirectoryExists("{$modulePath}/Domain/Services");
        $this->assertDirectoryExists("{$modulePath}/Domain/Events");
    }

    public function test_generate_creates_application_directories(): void
    {
        $result = $this->generator->generate('Payment');
        $modulePath = $result['path'];

        $this->assertDirectoryExists("{$modulePath}/Application/UseCases");
        $this->assertDirectoryExists("{$modulePath}/Application/DTOs");
        $this->assertDirectoryExists("{$modulePath}/Application/Commands");
        $this->assertDirectoryExists("{$modulePath}/Application/Queries");
    }

    public function test_generate_creates_infrastructure_directories(): void
    {
        $result = $this->generator->generate('Product');
        $modulePath = $result['path'];

        $this->assertDirectoryExists("{$modulePath}/Infrastructure/Persistence/Eloquent");
        $this->assertDirectoryExists("{$modulePath}/Infrastructure/Persistence/Repositories");
        $this->assertDirectoryExists("{$modulePath}/Infrastructure/Providers");
    }

    public function test_generate_creates_presentation_directories(): void
    {
        $result = $this->generator->generate('Catalog');
        $modulePath = $result['path'];

        $this->assertDirectoryExists("{$modulePath}/Presentation/Http/Controllers/Api");
        $this->assertDirectoryExists("{$modulePath}/Presentation/Http/Controllers/Web");
        $this->assertDirectoryExists("{$modulePath}/Presentation/Http/Routes");
    }

    public function test_generate_creates_stub_files(): void
    {
        $result = $this->generator->generate('Customer');
        $modulePath = $result['path'];

        $this->assertFileExists("{$modulePath}/Infrastructure/Providers/CustomerServiceProvider.php");
        $this->assertFileExists("{$modulePath}/Domain/Entities/CustomerEntity.php");
        $this->assertFileExists("{$modulePath}/Domain/ValueObjects/CustomerId.php");
        $this->assertFileExists("{$modulePath}/Domain/Repositories/CustomerRepositoryInterface.php");
        $this->assertFileExists("{$modulePath}/Application/UseCases/CreateCustomerUseCase.php");
        $this->assertFileExists("{$modulePath}/Application/DTOs/CreateCustomerDTO.php");
    }

    public function test_generate_studly_cases_module_name(): void
    {
        $result = $this->generator->generate('user-profile');
        $this->assertSame('UserProfile', $result['module']);
    }

    public function test_generate_creates_cqrs_files(): void
    {
        $result = $this->generator->generate('Order');
        $modulePath = $result['path'];

        $this->assertFileExists("{$modulePath}/Application/Commands/OrderCommand.php");
        $this->assertFileExists("{$modulePath}/Application/Queries/OrderQuery.php");
        $this->assertFileExists("{$modulePath}/Application/Handlers/OrderHandler.php");
    }

    public function test_generate_creates_domain_exception(): void
    {
        $result = $this->generator->generate('Product');
        $modulePath = $result['path'];

        $this->assertFileExists("{$modulePath}/Domain/Exceptions/ProductNotFoundException.php");
    }

    public function test_generate_creates_specification_and_enum(): void
    {
        $result = $this->generator->generate('Catalog');
        $modulePath = $result['path'];

        $this->assertFileExists("{$modulePath}/Domain/Specifications/CatalogIsActiveSpecification.php");
        $this->assertFileExists("{$modulePath}/Domain/Enums/CatalogStatus.php");
    }

    public function test_generate_creates_mapper_and_validator(): void
    {
        $result = $this->generator->generate('Payment');
        $modulePath = $result['path'];

        $this->assertFileExists("{$modulePath}/Application/Mappers/PaymentMapper.php");
        $this->assertFileExists("{$modulePath}/Application/Validators/PaymentValidator.php");
    }

    public function test_generate_creates_infrastructure_files(): void
    {
        $result = $this->generator->generate('Subscription');
        $modulePath = $result['path'];

        $this->assertFileExists("{$modulePath}/Infrastructure/Persistence/Factories/SubscriptionFactory.php");
        $this->assertFileExists("{$modulePath}/Infrastructure/Persistence/Seeders/SubscriptionSeeder.php");
        $this->assertFileExists("{$modulePath}/Infrastructure/Jobs/ProcessSubscriptionJob.php");
        $this->assertFileExists("{$modulePath}/Infrastructure/Events/SubscriptionCreatedListener.php");
        $this->assertFileExists("{$modulePath}/Infrastructure/Notifications/SubscriptionNotification.php");
    }

    public function test_generate_creates_presentation_files(): void
    {
        $result = $this->generator->generate('Campaign');
        $modulePath = $result['path'];

        $this->assertFileExists("{$modulePath}/Presentation/Console/Commands/CampaignConsoleCommand.php");
        $this->assertFileExists("{$modulePath}/Presentation/Http/Middleware/CampaignMiddleware.php");
    }

    public function test_build_replacements_contains_snake_plural_key(): void
    {
        $replacements = $this->generator->buildReplacements('UserActivity', 'App\\Modules\\UserActivity');
        $this->assertArrayHasKey('{{ moduleSnakePlural }}', $replacements);
        $this->assertSame('user_activities', $replacements['{{ moduleSnakePlural }}']);
    }
}
