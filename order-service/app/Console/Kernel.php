<?php

namespace App\Console;

use Illuminate\Console\Command;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Consumers\SagaConsumer;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\ConsumeSagaEvents::class,
    ];
}
