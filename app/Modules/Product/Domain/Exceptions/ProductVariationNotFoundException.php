<?php
declare(strict_types=1);
namespace Modules\Product\Domain\Exceptions;
use Modules\Core\Domain\Exceptions\NotFoundException;
class ProductVariationNotFoundException extends NotFoundException
{
    public function __construct(mixed $id = null) { parent::__construct('ProductVariation', $id); }
}
