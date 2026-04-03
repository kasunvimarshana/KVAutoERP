<?php
declare(strict_types=1);
namespace Modules\Supplier\Application\DTOs;
class SupplierData
{
    public int $tenant_id = 0; public string $name = ''; public string $code = ''; public ?int $user_id = null;
    public ?string $email = null; public ?string $phone = null; public ?array $address = null;
    public ?array $contact_person = null; public ?string $payment_terms = null; public string $currency = 'USD';
    public ?string $tax_number = null; public string $status = 'active'; public string $type = 'other';
    public ?array $attributes = null; public ?array $metadata = null;
    public static function fromArray(array $d): self { $o=new self(); foreach($d as $k=>$v) if(property_exists($o,$k)) $o->$k=$v; return $o; }
    public function toArray(): array { return get_object_vars($this); }
}
