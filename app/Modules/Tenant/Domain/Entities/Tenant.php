<?php
declare(strict_types=1);
namespace Modules\Tenant\Domain\Entities;
use Modules\Core\Domain\ValueObjects\DatabaseConfig;
use Modules\Core\Domain\ValueObjects\FeatureFlags;

class Tenant {
    private ?int $id;
    private string $name;
    private ?string $domain;
    private bool $active;
    private DatabaseConfig $databaseConfig;
    private ?FeatureFlags $featureFlags;
    private array $metadata;

    public function __construct(
        string $name,
        DatabaseConfig $databaseConfig,
        bool $active = true,
        ?int $id = null,
        ?string $domain = null,
        ?FeatureFlags $featureFlags = null,
        array $metadata = []
    ) {
        $this->name = $name;
        $this->databaseConfig = $databaseConfig;
        $this->active = $active;
        $this->id = $id;
        $this->domain = $domain;
        $this->featureFlags = $featureFlags;
        $this->metadata = $metadata;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDomain(): ?string { return $this->domain; }
    public function isActive(): bool { return $this->active; }
    public function getDatabaseConfig(): DatabaseConfig { return $this->databaseConfig; }
    public function getFeatureFlags(): ?FeatureFlags { return $this->featureFlags; }
    public function getMetadata(): array { return $this->metadata; }

    public function update(
        ?string $name = null,
        ?string $domain = null,
        ?DatabaseConfig $databaseConfig = null,
        ?bool $active = null
    ): void {
        if ($name !== null) $this->name = $name;
        if ($domain !== null) $this->domain = $domain;
        if ($databaseConfig !== null) $this->databaseConfig = $databaseConfig;
        if ($active !== null) $this->active = $active;
    }

    public function updateConfig(array $config): void {
        if (isset($config['feature_flags'])) {
            $this->featureFlags = new FeatureFlags($config['feature_flags']);
        }
        if (array_key_exists('active', $config)) {
            $this->active = (bool)$config['active'];
        }
        if (isset($config['metadata'])) {
            $this->metadata = $config['metadata'];
        }
    }
}
