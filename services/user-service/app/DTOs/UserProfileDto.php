<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class UserProfileDto
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $dateOfBirth = null,
        public ?string $gender = null,
        public ?string $bio = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $timezone = null,
        public ?string $language = null,
        public ?array $preferences = null,
        public ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            dateOfBirth: $data['date_of_birth'] ?? null,
            gender: $data['gender'] ?? null,
            bio: $data['bio'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            timezone: $data['timezone'] ?? null,
            language: $data['language'] ?? null,
            preferences: $data['preferences'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'first_name'    => $this->firstName,
            'last_name'     => $this->lastName,
            'date_of_birth' => $this->dateOfBirth,
            'gender'        => $this->gender,
            'bio'           => $this->bio,
            'address'       => $this->address,
            'city'          => $this->city,
            'country'       => $this->country,
            'timezone'      => $this->timezone,
            'language'      => $this->language,
            'preferences'   => $this->preferences,
            'metadata'      => $this->metadata,
        ], fn (mixed $value): bool => $value !== null);
    }
}
