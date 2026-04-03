<?php
declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Http\Controllers;
use Modules\Auth\Application\Contracts\SsoServiceInterface;
use Modules\Auth\Application\UseCases\GetAuthenticatedUser;
use Modules\Auth\Application\UseCases\LoginUser;
use Modules\Auth\Application\UseCases\LogoutUser;
use Modules\Auth\Application\UseCases\RegisterUser;
use Modules\User\Application\Contracts\FindUserServiceInterface;

class AuthController {
    public function __construct(
        private LoginUser $loginUser,
        private LogoutUser $logoutUser,
        private RegisterUser $registerUser,
        private SsoServiceInterface $ssoService,
        private FindUserServiceInterface $findUserService
    ) {}

    public function register() {}
    public function login() {}
    public function logout() {}
    public function me() {}
    public function refresh() {}
    public function forgotPassword() {}
    public function resetPassword() {}
    public function ssoExchange() {}
}
