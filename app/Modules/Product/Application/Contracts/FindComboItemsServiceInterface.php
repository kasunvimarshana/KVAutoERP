<?php
declare(strict_types=1);
namespace Modules\Product\Application\Contracts;
interface FindComboItemsServiceInterface { public function find(mixed $id): mixed; }
