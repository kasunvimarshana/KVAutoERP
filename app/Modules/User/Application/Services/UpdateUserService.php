<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Core\Domain\ValueObjects\Address;
use Modules\Core\Domain\ValueObjects\PhoneNumber;
use Modules\Core\Domain\ValueObjects\UserPreferences;
use Modules\User\Application\Contracts\UpdateUserServiceInterface;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserUpdated;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class UpdateUserService extends BaseService implements UpdateUserServiceInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->userRepository = $repository;
    }

    protected function handle(array $data): User
    {
        $id = $data['id'];

        $user = $this->userRepository->find($id);
        if (! $user) {
            throw new UserNotFoundException($id);
        }

        $firstName = array_key_exists('first_name', $data) ? (string) $data['first_name'] : $user->getFirstName();
        $lastName = array_key_exists('last_name', $data) ? (string) $data['last_name'] : $user->getLastName();

        $phone = array_key_exists('phone', $data)
            ? (! empty($data['phone']) ? new PhoneNumber((string) $data['phone']) : null)
            : $user->getPhone();

        $address = array_key_exists('address', $data)
            ? (! empty($data['address']) ? Address::fromArray($data['address']) : null)
            : $user->getAddress();

        $user->updateProfile($firstName, $lastName, $phone, $address);

        if (array_key_exists('preferences', $data) && is_array($data['preferences'])) {
            $preferences = new UserPreferences(
                $data['preferences']['language'] ?? $user->getPreferences()->getLanguage(),
                $data['preferences']['timezone'] ?? $user->getPreferences()->getTimezone(),
                $data['preferences']['notifications'] ?? $user->getPreferences()->getNotifications()
            );
            $user->updatePreferences($preferences);
        }

        if (array_key_exists('active', $data) && $user->isActive() !== (bool) $data['active']) {
            ((bool) $data['active']) ? $user->activate() : $user->deactivate();
        }

        $saved = $this->userRepository->save($user);

        if (array_key_exists('roles', $data) && is_array($data['roles'])) {
            $this->userRepository->syncRoles($saved, $data['roles']);
        }

        $this->addEvent(new UserUpdated($saved));

        return $saved;
    }
}
