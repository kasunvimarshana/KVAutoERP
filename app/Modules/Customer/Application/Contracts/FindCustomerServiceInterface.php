<?php declare(strict_types=1);
namespace Modules\Customer\Application\Contracts;
interface FindCustomerServiceInterface { public function execute(array $data=[]): mixed; }
