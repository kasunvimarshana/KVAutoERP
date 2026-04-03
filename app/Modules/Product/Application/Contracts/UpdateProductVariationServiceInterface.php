<?php
declare(strict_types=1);
namespace Modules\Product\Application\Contracts;
interface UpdateProductVariationServiceInterface { public function execute(array $data = []): mixed; }
