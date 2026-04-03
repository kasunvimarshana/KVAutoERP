<?php declare(strict_types=1);
namespace Modules\Customer\Application\Contracts;
interface CreateCustomerServiceInterface { public function execute(array $data=[]): mixed; }
