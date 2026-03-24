<?php

namespace Tests\Unit;

use Illuminate\Foundation\Auth\User;
// Auth domain
use Illuminate\Foundation\Http\FormRequest;
use Laravel\Passport\HasApiTokens;
use Modules\Auth\Application\Contracts\AuthenticationServiceInterface;
use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;
use Modules\Auth\Application\Contracts\AuthorizationStrategyInterface;
use Modules\Auth\Application\Contracts\LoginServiceInterface;
use Modules\Auth\Application\Contracts\LogoutServiceInterface;
// Auth application contracts
use Modules\Auth\Application\Contracts\RegisterUserServiceInterface;
use Modules\Auth\Application\Contracts\SsoServiceInterface;
use Modules\Auth\Application\Contracts\TokenServiceInterface;
use Modules\Auth\Application\DTOs\LoginData;
use Modules\Auth\Application\DTOs\RegisterData;
use Modules\Auth\Application\Services\AbacAuthorizationStrategy;
use Modules\Auth\Application\Services\AuthenticationService;
use Modules\Auth\Application\Services\AuthorizationService;
// Auth application services
use Modules\Auth\Application\Services\LoginService;
use Modules\Auth\Application\Services\LogoutService;
use Modules\Auth\Application\Services\PassportTokenService;
use Modules\Auth\Application\Services\RbacAuthorizationStrategy;
use Modules\Auth\Application\Services\RegisterUserService;
use Modules\Auth\Application\Services\SsoService;
use Modules\Auth\Application\UseCases\GetAuthenticatedUser;
use Modules\Auth\Application\UseCases\LoginUser;
use Modules\Auth\Application\UseCases\LogoutUser;
// Auth application DTOs
use Modules\Auth\Application\UseCases\RegisterUser;
use Modules\Auth\Domain\Entities\AccessToken;
// Auth application use cases
use Modules\Auth\Domain\Events\UserLoggedIn;
use Modules\Auth\Domain\Events\UserLoggedOut;
use Modules\Auth\Domain\Events\UserRegistered;
use Modules\Auth\Domain\Exceptions\AuthenticationException;
// Auth infrastructure
use Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use Modules\Auth\Domain\Exceptions\TokenExpiredException;
use Modules\Auth\Infrastructure\Http\Controllers\AuthController;
use Modules\Auth\Infrastructure\Http\Middleware\CheckPermission;
use Modules\Auth\Infrastructure\Http\Middleware\CheckRole;
use Modules\Auth\Infrastructure\Http\Requests\LoginRequest;
use Modules\Auth\Infrastructure\Http\Requests\RegisterRequest;
use Modules\Auth\Infrastructure\Http\Requests\SsoRequest;
use Modules\Auth\Infrastructure\Http\Resources\AuthTokenResource;
use Modules\Auth\Infrastructure\Providers\AuthModuleServiceProvider;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;
use PHPUnit\Framework\TestCase;

class AuthModuleTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Domain: Entities
    // -------------------------------------------------------------------------

    public function test_access_token_entity_exists(): void
    {
        $this->assertTrue(class_exists(AccessToken::class));
    }

    public function test_access_token_can_be_constructed_and_serialized(): void
    {
        $token = new AccessToken(
            accessToken: 'test-token-value',
            tokenType: 'Bearer',
            expiresIn: 3600,
            refreshToken: 'refresh-token',
            scopes: ['*'],
        );

        $this->assertSame('test-token-value', $token->getAccessToken());
        $this->assertSame('Bearer', $token->getTokenType());
        $this->assertSame(3600, $token->getExpiresIn());
        $this->assertSame('refresh-token', $token->getRefreshToken());
        $this->assertSame(['*'], $token->getScopes());

        $array = $token->toArray();
        $this->assertArrayHasKey('access_token', $array);
        $this->assertArrayHasKey('token_type', $array);
        $this->assertArrayHasKey('expires_in', $array);
    }

    public function test_access_token_without_refresh_token(): void
    {
        $token = new AccessToken('tok', 'Bearer', 900);
        $this->assertNull($token->getRefreshToken());
        $this->assertSame([], $token->getScopes());
    }

    // -------------------------------------------------------------------------
    // Domain: Events
    // -------------------------------------------------------------------------

    public function test_auth_event_classes_exist(): void
    {
        $this->assertTrue(class_exists(UserLoggedIn::class));
        $this->assertTrue(class_exists(UserLoggedOut::class));
        $this->assertTrue(class_exists(UserRegistered::class));
    }

    public function test_user_logged_in_event_stores_properties(): void
    {
        $event = new UserLoggedIn(42, 'alice@example.com', '127.0.0.1', 'TestAgent/1.0');
        $this->assertSame(42, $event->userId);
        $this->assertSame('alice@example.com', $event->email);
        $this->assertSame('127.0.0.1', $event->ipAddress);
    }

    public function test_user_logged_out_event_stores_properties(): void
    {
        $event = new UserLoggedOut(7, 'bob@example.com');
        $this->assertSame(7, $event->userId);
        $this->assertSame('bob@example.com', $event->email);
    }

    public function test_user_registered_event_stores_properties(): void
    {
        $event = new UserRegistered(1, 'carol@example.com', 'Carol', 'Smith');
        $this->assertSame(1, $event->userId);
        $this->assertSame('Carol', $event->firstName);
        $this->assertSame('Smith', $event->lastName);
    }

    // -------------------------------------------------------------------------
    // Domain: Exceptions
    // -------------------------------------------------------------------------

    public function test_auth_exception_classes_exist(): void
    {
        $this->assertTrue(class_exists(AuthenticationException::class));
        $this->assertTrue(class_exists(InvalidCredentialsException::class));
        $this->assertTrue(class_exists(TokenExpiredException::class));
    }

    public function test_invalid_credentials_extends_authentication_exception(): void
    {
        $this->assertTrue(
            is_subclass_of(InvalidCredentialsException::class, AuthenticationException::class)
        );
    }

    public function test_token_expired_extends_authentication_exception(): void
    {
        $this->assertTrue(
            is_subclass_of(TokenExpiredException::class, AuthenticationException::class)
        );
    }

    public function test_authentication_exception_extends_domain_exception(): void
    {
        $this->assertTrue(
            is_subclass_of(AuthenticationException::class, DomainException::class)
        );
    }

    public function test_exceptions_carry_correct_default_messages(): void
    {
        $auth = new AuthenticationException;
        $this->assertSame('Authentication failed', $auth->getMessage());

        $creds = new InvalidCredentialsException;
        $this->assertSame('Invalid credentials provided', $creds->getMessage());

        $expired = new TokenExpiredException;
        $this->assertSame('Token has expired', $expired->getMessage());
    }

    // -------------------------------------------------------------------------
    // Application: Contracts / Interfaces
    // -------------------------------------------------------------------------

    public function test_all_auth_service_interfaces_exist(): void
    {
        $this->assertTrue(interface_exists(AuthenticationServiceInterface::class));
        $this->assertTrue(interface_exists(AuthorizationServiceInterface::class));
        $this->assertTrue(interface_exists(AuthorizationStrategyInterface::class));
        $this->assertTrue(interface_exists(LoginServiceInterface::class));
        $this->assertTrue(interface_exists(LogoutServiceInterface::class));
        $this->assertTrue(interface_exists(RegisterUserServiceInterface::class));
        $this->assertTrue(interface_exists(SsoServiceInterface::class));
        $this->assertTrue(interface_exists(TokenServiceInterface::class));
    }

    // -------------------------------------------------------------------------
    // Application: Service Implementations
    // -------------------------------------------------------------------------

    public function test_all_auth_service_implementations_exist(): void
    {
        $this->assertTrue(class_exists(AuthenticationService::class));
        $this->assertTrue(class_exists(AuthorizationService::class));
        $this->assertTrue(class_exists(LoginService::class));
        $this->assertTrue(class_exists(LogoutService::class));
        $this->assertTrue(class_exists(PassportTokenService::class));
        $this->assertTrue(class_exists(RbacAuthorizationStrategy::class));
        $this->assertTrue(class_exists(AbacAuthorizationStrategy::class));
        $this->assertTrue(class_exists(RegisterUserService::class));
        $this->assertTrue(class_exists(SsoService::class));
    }

    public function test_service_implementations_implement_their_interfaces(): void
    {
        $this->assertTrue(
            is_subclass_of(AuthenticationService::class, AuthenticationServiceInterface::class)
        );
        $this->assertTrue(
            is_subclass_of(AuthorizationService::class, AuthorizationServiceInterface::class)
        );
        $this->assertTrue(
            is_subclass_of(LoginService::class, LoginServiceInterface::class)
        );
        $this->assertTrue(
            is_subclass_of(LogoutService::class, LogoutServiceInterface::class)
        );
        $this->assertTrue(
            is_subclass_of(PassportTokenService::class, TokenServiceInterface::class)
        );
        $this->assertTrue(
            is_subclass_of(RbacAuthorizationStrategy::class, AuthorizationStrategyInterface::class)
        );
        $this->assertTrue(
            is_subclass_of(AbacAuthorizationStrategy::class, AuthorizationStrategyInterface::class)
        );
        $this->assertTrue(
            is_subclass_of(RegisterUserService::class, RegisterUserServiceInterface::class)
        );
        $this->assertTrue(
            is_subclass_of(SsoService::class, SsoServiceInterface::class)
        );
    }

    public function test_rbac_strategy_returns_correct_name(): void
    {
        $strategy = new RbacAuthorizationStrategy;
        $this->assertSame('rbac', $strategy->getName());
    }

    public function test_abac_strategy_returns_correct_name(): void
    {
        $strategy = new AbacAuthorizationStrategy;
        $this->assertSame('abac', $strategy->getName());
    }

    // -------------------------------------------------------------------------
    // Application: DTOs
    // -------------------------------------------------------------------------

    public function test_auth_dto_classes_exist(): void
    {
        $this->assertTrue(class_exists(LoginData::class));
        $this->assertTrue(class_exists(RegisterData::class));
    }

    public function test_login_data_dto_has_expected_rules(): void
    {
        $dto = new LoginData;
        $rules = $dto->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_register_data_dto_has_expected_rules(): void
    {
        $dto = new RegisterData;
        $rules = $dto->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('last_name', $rules);
        $this->assertArrayHasKey('tenant_id', $rules);
    }

    public function test_login_data_from_array(): void
    {
        $dto = LoginData::fromArray(['email' => 'alice@example.com', 'password' => 'secret123']);
        $this->assertSame('alice@example.com', $dto->email);
        $this->assertSame('secret123', $dto->password);
    }

    public function test_register_data_from_array(): void
    {
        $dto = RegisterData::fromArray([
            'tenant_id' => 1,
            'email' => 'alice@example.com',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'password' => 'secret123',
        ]);
        $this->assertSame(1, $dto->tenant_id);
        $this->assertSame('alice@example.com', $dto->email);
    }

    // -------------------------------------------------------------------------
    // Application: Use Cases
    // -------------------------------------------------------------------------

    public function test_auth_use_case_classes_exist(): void
    {
        $this->assertTrue(class_exists(LoginUser::class));
        $this->assertTrue(class_exists(LogoutUser::class));
        $this->assertTrue(class_exists(RegisterUser::class));
        $this->assertTrue(class_exists(GetAuthenticatedUser::class));
    }

    public function test_login_user_use_case_accepts_login_service_in_constructor(): void
    {
        $reflection = new \ReflectionClass(LoginUser::class);
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $params = $constructor->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame(
            LoginServiceInterface::class,
            $params[0]->getType()?->getName()
        );
    }

    public function test_logout_user_use_case_accepts_logout_service_in_constructor(): void
    {
        $reflection = new \ReflectionClass(LogoutUser::class);
        $params = $reflection->getConstructor()->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame(
            LogoutServiceInterface::class,
            $params[0]->getType()?->getName()
        );
    }

    public function test_register_user_use_case_accepts_register_and_login_services_in_constructor(): void
    {
        $reflection = new \ReflectionClass(RegisterUser::class);
        $params = $reflection->getConstructor()->getParameters();

        $this->assertCount(2, $params);
        $this->assertSame(RegisterUserServiceInterface::class, $params[0]->getType()?->getName());
        $this->assertSame(LoginServiceInterface::class, $params[1]->getType()?->getName());
    }

    // -------------------------------------------------------------------------
    // Infrastructure: Controllers, Middleware, Requests, Resources, Provider
    // -------------------------------------------------------------------------

    public function test_auth_infrastructure_classes_exist(): void
    {
        $this->assertTrue(class_exists(AuthController::class));
        $this->assertTrue(class_exists(CheckRole::class));
        $this->assertTrue(class_exists(CheckPermission::class));
        $this->assertTrue(class_exists(LoginRequest::class));
        $this->assertTrue(class_exists(RegisterRequest::class));
        $this->assertTrue(class_exists(SsoRequest::class));
        $this->assertTrue(class_exists(AuthTokenResource::class));
        $this->assertTrue(class_exists(AuthModuleServiceProvider::class));
    }

    public function test_auth_controller_constructor_uses_interfaces(): void
    {
        $reflection = new \ReflectionClass(AuthController::class);
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $params = $constructor->getParameters();
        $this->assertGreaterThanOrEqual(4, count($params));

        $types = array_map(fn ($p) => $p->getType()?->getName(), $params);

        // Controller injects use case classes and the SSO service interface for proper DI
        $this->assertContains(LoginUser::class, $types);
        $this->assertContains(LogoutUser::class, $types);
        $this->assertContains(RegisterUser::class, $types);
        $this->assertContains(SsoServiceInterface::class, $types);
    }

    public function test_check_role_middleware_constructor_uses_authorization_service_interface(): void
    {
        $reflection = new \ReflectionClass(CheckRole::class);
        $params = $reflection->getConstructor()->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame(
            AuthorizationServiceInterface::class,
            $params[0]->getType()?->getName()
        );
    }

    public function test_check_permission_middleware_constructor_uses_authorization_service_interface(): void
    {
        $reflection = new \ReflectionClass(CheckPermission::class);
        $params = $reflection->getConstructor()->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame(
            AuthorizationServiceInterface::class,
            $params[0]->getType()?->getName()
        );
    }

    // -------------------------------------------------------------------------
    // UserModel Passport integration
    // -------------------------------------------------------------------------

    public function test_user_model_uses_has_api_tokens(): void
    {
        $traits = class_uses_recursive(
            UserModel::class
        );
        $this->assertArrayHasKey(
            HasApiTokens::class,
            $traits,
            'UserModel must use HasApiTokens for Passport integration.'
        );
    }

    public function test_user_model_extends_authenticatable(): void
    {
        $this->assertTrue(
            is_subclass_of(
                UserModel::class,
                User::class
            ),
            'UserModel must extend Illuminate\Foundation\Auth\User to work with Passport.'
        );
    }

    // -------------------------------------------------------------------------
    // Auth routes file exists
    // -------------------------------------------------------------------------

    public function test_auth_routes_file_exists(): void
    {
        $path = dirname(__DIR__, 2).'/app/Modules/Auth/routes/api.php';
        $this->assertTrue(file_exists($path), 'Auth module routes file must exist.');
    }

    public function test_sso_request_has_expected_rules(): void
    {
        $request = new SsoRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('token', $rules);
    }

    public function test_sso_request_is_form_request(): void
    {
        $this->assertInstanceOf(FormRequest::class, new SsoRequest);
    }
}
