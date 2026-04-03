<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Domain\Entities;
use Modules\Core\Domain\ValueObjects\Code;
use Modules\Core\Domain\ValueObjects\Metadata;
use Modules\Core\Domain\ValueObjects\Name;

class OrganizationUnit {
    private ?int $id;
    private int $tenantId;
    private Name $name;
    private ?Code $code;
    private ?int $parentId;
    private string $type;
    private ?string $description;
    private ?Metadata $metadata;
    private array $children = [];

    public function __construct(
        int $tenantId,
        Name $name,
        ?Code $code = null,
        ?int $parentId = null,
        string $type = 'department',
        ?int $id = null,
        ?string $description = null,
        ?Metadata $metadata = null
    ) {
        $this->tenantId = $tenantId;
        $this->name = $name;
        $this->code = $code;
        $this->parentId = $parentId;
        $this->type = $type;
        $this->id = $id;
        $this->description = $description;
        $this->metadata = $metadata;
    }

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): Name { return $this->name; }
    public function getCode(): ?Code { return $this->code; }
    public function getType(): string { return $this->type; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getDescription(): ?string { return $this->description; }
    public function getMetadata(): Metadata { return $this->metadata ?? new Metadata(); }
    public function getChildren(): array { return $this->children; }

    public function updateDetails(Name|string $name, ?Code $code, ?string $description, ?Metadata $metadata): void {
        $this->name = $name instanceof Name ? $name : new Name($name);
        $this->code = $code;
        $this->description = $description;
        $this->metadata = $metadata ?? new Metadata();
    }

    public function addChild(OrganizationUnit $child): void {
        $this->children[] = $child;
    }

    public function removeChild(OrganizationUnit $child): void {
        $this->children = array_values(array_filter($this->children, fn($c) => $c !== $child));
    }

    public function moveTo(int $newParentId): void {
        $this->parentId = $newParentId;
    }
}
