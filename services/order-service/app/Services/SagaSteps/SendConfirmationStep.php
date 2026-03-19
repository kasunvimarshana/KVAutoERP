<?php

namespace App\Services\SagaSteps;

use Shared\Core\Contracts\SagaStepInterface;
use Shared\Core\Services\ExternalServiceClient;
use Illuminate\Support\Facades\Log;

class SendConfirmationStep implements SagaStepInterface
{
    /**
     * Handle the Saga step
     */
    public function handle(array $data): bool|array
    {
        Log::info("Executing SendConfirmationStep");
        
        // In a real microservices system, you would call a notification service
        // or the user service to send a welcome/confirmation email.
        $userClient = new ExternalServiceClient('http://user-service/api/v1/users');
        $customer = $userClient->get("/{$data['customer_id']}");

        if ($customer && isset($customer['status']) && $customer['status'] === 'success') {
            Log::info("Order confirmation sent to: {$customer['data']['email']}");
            return ['customer_email' => $customer['data']['email']];
        }

        // We can treat this as an optional step or mandatory
        return true; 
    }

    /**
     * Rollback the Saga step
     */
    public function rollback(array $data): bool
    {
        Log::warning("Rolling back SendConfirmationStep (if any)");
        // Compensation could be sending a 'cancellation' email
        return true;
    }
}
