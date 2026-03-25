<?php

namespace Tests\Unit;

use Modules\Core\Infrastructure\ApiDoc\Contracts\ApiDocServiceInterface;
use Modules\Core\Infrastructure\ApiDoc\OpenApiSpec;
use Modules\Core\Infrastructure\ApiDoc\Services\SwaggerApiDocService;
use Modules\Auth\Infrastructure\Http\Controllers\AuthController;
use Modules\OrganizationUnit\Infrastructure\Http\Controllers\OrganizationUnitAttachmentController;
use Modules\OrganizationUnit\Infrastructure\Http\Controllers\OrganizationUnitController;
use Modules\Tenant\Infrastructure\Http\Controllers\TenantAttachmentController;
use Modules\Tenant\Infrastructure\Http\Controllers\TenantController;
use Modules\User\Infrastructure\Http\Controllers\PermissionController;
use Modules\User\Infrastructure\Http\Controllers\RoleController;
use Modules\User\Infrastructure\Http\Controllers\UserAttachmentController;
use Modules\User\Infrastructure\Http\Controllers\UserController;
use OpenApi\Attributes as OA;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SwaggerApiDocTest extends TestCase
{
    // ── Infrastructure ────────────────────────────────────────────────────────

    /**
     * Verify the ApiDocServiceInterface contract exists.
     */
    public function test_api_doc_service_interface_exists(): void
    {
        $this->assertTrue(interface_exists(ApiDocServiceInterface::class));
    }

    /**
     * Verify SwaggerApiDocService implements ApiDocServiceInterface.
     */
    public function test_swagger_api_doc_service_implements_interface(): void
    {
        $this->assertTrue(class_exists(SwaggerApiDocService::class));
        $this->assertTrue(
            in_array(ApiDocServiceInterface::class, class_implements(SwaggerApiDocService::class), true),
        );
    }

    /**
     * Verify SwaggerApiDocService exposes the expected public methods.
     */
    public function test_swagger_api_doc_service_has_required_methods(): void
    {
        $reflection = new \ReflectionClass(SwaggerApiDocService::class);

        $this->assertTrue($reflection->hasMethod('generate'));
        $this->assertTrue($reflection->hasMethod('uiUrl'));
        $this->assertTrue($reflection->hasMethod('specUrl'));

        $this->assertTrue($reflection->getMethod('generate')->isPublic());
        $this->assertTrue($reflection->getMethod('uiUrl')->isPublic());
        $this->assertTrue($reflection->getMethod('specUrl')->isPublic());
    }

    /**
     * Verify the global OpenApiSpec class exists and is not abstract.
     */
    public function test_open_api_spec_class_exists(): void
    {
        $this->assertTrue(class_exists(OpenApiSpec::class));

        $reflection = new \ReflectionClass(OpenApiSpec::class);
        $this->assertFalse($reflection->isAbstract());
    }

    // ── Global attributes on OpenApiSpec ──────────────────────────────────────

    /**
     * Verify OpenApiSpec carries the top-level OA\Info attribute.
     */
    public function test_open_api_spec_has_info_attribute(): void
    {
        $reflection = new \ReflectionClass(OpenApiSpec::class);
        $attributes = $reflection->getAttributes(OA\Info::class);

        $this->assertNotEmpty($attributes, 'OpenApiSpec must carry an #[OA\Info] attribute.');
    }

    /**
     * Verify OpenApiSpec carries at least one OA\Server attribute.
     */
    public function test_open_api_spec_has_server_attribute(): void
    {
        $reflection = new \ReflectionClass(OpenApiSpec::class);
        $attributes = $reflection->getAttributes(OA\Server::class);

        $this->assertNotEmpty($attributes, 'OpenApiSpec must carry at least one #[OA\Server] attribute.');
    }

    /**
     * Verify OpenApiSpec carries the bearerAuth security scheme.
     */
    public function test_open_api_spec_has_security_scheme_attribute(): void
    {
        $reflection = new \ReflectionClass(OpenApiSpec::class);
        $attributes = $reflection->getAttributes(OA\SecurityScheme::class);

        $this->assertNotEmpty($attributes, 'OpenApiSpec must carry an #[OA\SecurityScheme] attribute.');

        /** @var OA\SecurityScheme $scheme */
        $scheme = $attributes[0]->newInstance();
        $this->assertSame('bearerAuth', $scheme->securityScheme);
        $this->assertSame('http', $scheme->type);
        $this->assertSame('bearer', $scheme->scheme);
    }

    /**
     * Verify OpenApiSpec defines all expected tags.
     */
    public function test_open_api_spec_has_all_module_tags(): void
    {
        $reflection = new \ReflectionClass(OpenApiSpec::class);
        $tagAttributes = $reflection->getAttributes(OA\Tag::class);

        $tagNames = array_map(
            fn (\ReflectionAttribute $a) => $a->newInstance()->name,
            $tagAttributes,
        );

        $expectedTags = [
            'Auth',
            'Users',
            'Roles',
            'Permissions',
            'User Attachments',
            'Tenants',
            'Tenant Attachments',
            'Organization Units',
            'OrgUnit Attachments',
        ];

        foreach ($expectedTags as $tag) {
            $this->assertContains($tag, $tagNames, "Tag '{$tag}' is missing from OpenApiSpec.");
        }
    }

    /**
     * Verify OpenApiSpec defines all expected reusable schemas.
     */
    public function test_open_api_spec_has_all_reusable_schemas(): void
    {
        $reflection = new \ReflectionClass(OpenApiSpec::class);
        $schemaAttributes = $reflection->getAttributes(OA\Schema::class);

        $schemaNames = array_map(
            fn (\ReflectionAttribute $a) => $a->newInstance()->schema,
            $schemaAttributes,
        );

        $expectedSchemas = [
            'ErrorResponse',
            'ValidationErrorResponse',
            'MessageResponse',
            'PaginationMeta',
            'PaginationLinks',
            'AuthTokenResponse',
            'AddressObject',
            'UserPreferencesObject',
            'PermissionObject',
            'RoleObject',
            'UserObject',
            'AttachmentObject',
            'DatabaseConfigObject',
            'TenantObject',
            'TenantConfigObject',
            'OrganizationUnitObject',
        ];

        foreach ($expectedSchemas as $schema) {
            $this->assertContains($schema, $schemaNames, "Schema '{$schema}' is missing from OpenApiSpec.");
        }
    }

    // ── Per-controller OA annotation coverage ────────────────────────────────

    /**
     * Verify AuthController methods are annotated with OA operations.
     */
    public function test_auth_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(AuthController::class, [
            'register',
            'login',
            'logout',
            'me',
            'refresh',
            'forgotPassword',
            'resetPassword',
            'ssoExchange',
        ]);
    }

    /**
     * Verify UserController methods are annotated with OA operations.
     */
    public function test_user_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(UserController::class, [
            'index',
            'store',
            'show',
            'update',
            'destroy',
            'assignRole',
            'updatePreferences',
        ]);
    }

    /**
     * Verify RoleController methods are annotated with OA operations.
     */
    public function test_role_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(RoleController::class, [
            'index',
            'show',
            'store',
            'destroy',
            'syncPermissions',
        ]);
    }

    /**
     * Verify PermissionController methods are annotated with OA operations.
     */
    public function test_permission_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(PermissionController::class, [
            'index',
            'show',
            'store',
            'destroy',
        ]);
    }

    /**
     * Verify UserAttachmentController methods are annotated with OA operations.
     */
    public function test_user_attachment_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(UserAttachmentController::class, [
            'index',
            'store',
            'destroy',
            'serve',
        ]);
    }

    /**
     * Verify TenantController methods are annotated with OA operations.
     */
    public function test_tenant_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(TenantController::class, [
            'index',
            'store',
            'show',
            'update',
            'destroy',
            'updateConfig',
            'configByDomain',
        ]);
    }

    /**
     * Verify TenantAttachmentController methods are annotated with OA operations.
     */
    public function test_tenant_attachment_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(TenantAttachmentController::class, [
            'index',
            'store',
            'destroy',
            'serve',
        ]);
    }

    /**
     * Verify OrganizationUnitController methods are annotated with OA operations.
     */
    public function test_organization_unit_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(OrganizationUnitController::class, [
            'index',
            'store',
            'show',
            'update',
            'destroy',
            'tree',
            'move',
        ]);
    }

    /**
     * Verify OrganizationUnitAttachmentController methods are annotated with OA operations.
     */
    public function test_organization_unit_attachment_controller_methods_have_oa_annotations(): void
    {
        $this->assertControllerMethodsHaveOaAnnotations(OrganizationUnitAttachmentController::class, [
            'index',
            'store',
            'destroy',
            'serve',
        ]);
    }

    // ── OA\Info attribute values ───────────────────────────────────────────────

    /**
     * Verify the OA\Info attribute on OpenApiSpec has a non-empty title and version.
     */
    public function test_open_api_info_has_title_and_version(): void
    {
        $reflection = new \ReflectionClass(OpenApiSpec::class);
        $attributes = $reflection->getAttributes(OA\Info::class);

        $this->assertNotEmpty($attributes);

        /** @var OA\Info $info */
        $info = $attributes[0]->newInstance();

        $this->assertNotEmpty($info->title, 'OA\Info title must not be empty.');
        $this->assertNotEmpty($info->version, 'OA\Info version must not be empty.');
    }

    // ── Schema accuracy ───────────────────────────────────────────────────────

    /**
     * Verify UserPreferencesObject schema uses the actual domain fields
     * (language, timezone, notifications) not legacy stubs.
     */
    public function test_user_preferences_schema_has_correct_fields(): void
    {
        $reflection = new \ReflectionClass(OpenApiSpec::class);
        $schemaAttributes = $reflection->getAttributes(OA\Schema::class);

        $prefSchema = null;
        foreach ($schemaAttributes as $attr) {
            $instance = $attr->newInstance();
            if ($instance->schema === 'UserPreferencesObject') {
                $prefSchema = $instance;
                break;
            }
        }

        $this->assertNotNull($prefSchema, 'UserPreferencesObject schema must exist.');

        $propertyNames = array_map(
            fn (OA\Property $p) => $p->property,
            $prefSchema->properties,
        );

        $this->assertContains('language',      $propertyNames, "UserPreferencesObject must have 'language' property.");
        $this->assertContains('timezone',      $propertyNames, "UserPreferencesObject must have 'timezone' property.");
        $this->assertContains('notifications', $propertyNames, "UserPreferencesObject must have 'notifications' property.");

        $this->assertNotContains('locale', $propertyNames, "UserPreferencesObject must not have legacy 'locale' property.");
        $this->assertNotContains('theme',  $propertyNames, "UserPreferencesObject must not have legacy 'theme' property.");
    }

    // ── Store endpoints return HTTP 201 ───────────────────────────────────────

    /**
     * Verify that every store/create operation documents a 201 Created response.
     */
    #[DataProvider('storeOperationProvider')]
    public function test_store_operations_document_201_response(string $controllerClass, string $method): void
    {
        $reflection = new \ReflectionClass($controllerClass);
        $this->assertTrue($reflection->hasMethod($method), "{$controllerClass}::{$method} must exist.");

        $methodRef = $reflection->getMethod($method);

        $oaPostAttrs = $methodRef->getAttributes(OA\Post::class);
        $this->assertNotEmpty($oaPostAttrs, "{$controllerClass}::{$method} must carry an #[OA\\Post] attribute.");

        /** @var OA\Post $post */
        $post = $oaPostAttrs[0]->newInstance();

        $responseCodes = array_map(
            fn (OA\Response $r) => (int) $r->response,
            $post->responses,
        );

        $this->assertContains(
            201,
            $responseCodes,
            "{$controllerClass}::{$method} must document a 201 Created response.",
        );
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    public static function storeOperationProvider(): array
    {
        return [
            'UserController::store'             => [UserController::class,             'store'],
            'TenantController::store'           => [TenantController::class,           'store'],
            'OrganizationUnitController::store' => [OrganizationUnitController::class, 'store'],
            'RoleController::store'             => [RoleController::class,             'store'],
            'PermissionController::store'       => [PermissionController::class,       'store'],
        ];
    }

    // ── Request body accuracy ─────────────────────────────────────────────────

    /**
     * Verify TenantController::store documents database_config as a required field.
     */
    public function test_tenant_store_documents_database_config(): void
    {
        $jsonContent = $this->getStoreRequestBodyContent(TenantController::class);

        $this->assertNotNull($jsonContent, 'TenantController::store must have a JSON request body.');

        $required = $jsonContent->required ?? [];
        $this->assertContains(
            'database_config',
            $required,
            "TenantController::store requestBody must list 'database_config' as required.",
        );
    }

    /**
     * Verify UserController::store does not document password/password_confirmation
     * (those fields are only for authentication registration, not admin user creation).
     */
    public function test_user_store_does_not_document_password_fields(): void
    {
        $jsonContent = $this->getStoreRequestBodyContent(UserController::class);

        $this->assertNotNull($jsonContent, 'UserController::store must have a JSON request body.');

        $propertyNames = array_map(
            fn (OA\Property $p) => $p->property,
            $jsonContent->properties ?? [],
        );

        $this->assertNotContains(
            'password',
            $propertyNames,
            "UserController::store must not document a 'password' field (not a validation rule for admin user creation).",
        );
        $this->assertNotContains(
            'password_confirmation',
            $propertyNames,
            "UserController::store must not document a 'password_confirmation' field.",
        );
    }

    // ── l5-swagger config ─────────────────────────────────────────────────────

    /**
     * Verify the l5-swagger config file exists and contains required keys.
     */
    public function test_l5_swagger_config_file_exists_and_is_valid(): void
    {
        $configPath = dirname(__DIR__, 2).'/config/l5-swagger.php';

        $this->assertFileExists($configPath, 'config/l5-swagger.php must exist.');

        $contents = file_get_contents($configPath);
        $this->assertIsString($contents);

        // Verify top-level config keys are present in the file source.
        $this->assertStringContainsString("'default'", $contents);
        $this->assertStringContainsString("'documentations'", $contents);
        $this->assertStringContainsString("'defaults'", $contents);
        $this->assertStringContainsString("'annotations'", $contents);
    }

    /**
     * Verify the annotations path in config points to the app directory.
     */
    public function test_l5_swagger_config_annotations_path_includes_app(): void
    {
        $configPath = dirname(__DIR__, 2).'/config/l5-swagger.php';
        $contents = (string) file_get_contents($configPath);

        // The annotations array must reference base_path('app') or base_path("app").
        $this->assertTrue(
            str_contains($contents, "base_path('app')") || str_contains($contents, 'base_path("app")'),
            "The 'annotations' key must include base_path('app').",
        );
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    /**
     * Assert that every listed method on a controller class carries at least one
     * OA HTTP-operation attribute (Get, Post, Put, Patch, Delete, Head, Options).
     */
    private function assertControllerMethodsHaveOaAnnotations(string $controllerClass, array $methods): void
    {
        $this->assertTrue(class_exists($controllerClass), "Controller class {$controllerClass} must exist.");

        $reflection = new \ReflectionClass($controllerClass);

        $oaOperationClasses = [
            OA\Get::class,
            OA\Post::class,
            OA\Put::class,
            OA\Patch::class,
            OA\Delete::class,
            OA\Head::class,
            OA\Options::class,
        ];

        foreach ($methods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Method {$controllerClass}::{$methodName} must exist.",
            );

            $method = $reflection->getMethod($methodName);
            $hasOaAttr = false;

            foreach ($oaOperationClasses as $oaClass) {
                if (! empty($method->getAttributes($oaClass))) {
                    $hasOaAttr = true;
                    break;
                }
            }

            $this->assertTrue(
                $hasOaAttr,
                "Method {$controllerClass}::{$methodName} must have an OA HTTP-operation attribute.",
            );
        }
    }

    /**
     * Retrieve the OA\JsonContent from the store() method's OA\Post requestBody.
     *
     * The l5-swagger attribute system stores the JsonContent in the internal
     * `_unmerged` array of the RequestBody object rather than the `content` property.
     */
    private function getStoreRequestBodyContent(string $controllerClass): ?OA\JsonContent
    {
        $reflection = new \ReflectionClass($controllerClass);
        $methodRef  = $reflection->getMethod('store');

        $oaPostAttrs = $methodRef->getAttributes(OA\Post::class);
        if (empty($oaPostAttrs)) {
            return null;
        }

        /** @var OA\Post $post */
        $post = $oaPostAttrs[0]->newInstance();

        if (! $post->requestBody instanceof OA\RequestBody) {
            return null;
        }

        $rbRef       = new \ReflectionObject($post->requestBody);
        $unmergedProp = $rbRef->getProperty('_unmerged');
        $unmergedProp->setAccessible(true);
        $unmerged = $unmergedProp->getValue($post->requestBody);

        foreach ($unmerged as $item) {
            if ($item instanceof OA\JsonContent) {
                return $item;
            }
        }

        return null;
    }
}
