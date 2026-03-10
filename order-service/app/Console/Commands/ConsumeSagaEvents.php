<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Consumers\SagaConsumer;

class ConsumeSagaEvents extends Command
{
    protected $signature   = 'saga:consume';
    protected $description = 'Start the Saga event consumer';

    public function handle(): void
    {
        $this->info('[order-service] Starting Saga consumer...');
        SagaConsumer::run();
    }
}
