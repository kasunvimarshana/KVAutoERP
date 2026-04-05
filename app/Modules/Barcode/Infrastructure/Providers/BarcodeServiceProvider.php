<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Barcode\Application\Contracts\BarcodePrinterDispatcherInterface;
use Modules\Barcode\Application\Contracts\BarcodeGeneratorServiceInterface;
use Modules\Barcode\Application\Contracts\BarcodeScannerServiceInterface;
use Modules\Barcode\Application\Contracts\LabelTemplateServiceInterface;
use Modules\Barcode\Application\Drivers\EplPrinterDriver;
use Modules\Barcode\Application\Drivers\SvgPrinterDriver;
use Modules\Barcode\Application\Drivers\ZplPrinterDriver;
use Modules\Barcode\Application\Services\BarcodePrinterDispatcher;
use Modules\Barcode\Application\Services\BarcodeGeneratorService;
use Modules\Barcode\Application\Services\BarcodeScannerService;
use Modules\Barcode\Application\Services\LabelTemplateService;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodePrintJobRepositoryInterface;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodeRepositoryInterface;
use Modules\Barcode\Domain\RepositoryInterfaces\LabelTemplateRepositoryInterface;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Models\BarcodePrintJobModel;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Models\BarcodeModel;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Models\LabelTemplateModel;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Repositories\EloquentBarcodePrintJobRepository;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Repositories\EloquentBarcodeRepository;
use Modules\Barcode\Infrastructure\Persistence\Eloquent\Repositories\EloquentLabelTemplateRepository;

class BarcodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BarcodeRepositoryInterface::class, function ($app) {
            return new EloquentBarcodeRepository($app->make(BarcodeModel::class));
        });

        $this->app->bind(LabelTemplateRepositoryInterface::class, function ($app) {
            return new EloquentLabelTemplateRepository($app->make(LabelTemplateModel::class));
        });

        $this->app->bind(BarcodePrintJobRepositoryInterface::class, function ($app) {
            return new EloquentBarcodePrintJobRepository($app->make(BarcodePrintJobModel::class));
        });

        $this->app->bind(BarcodeGeneratorServiceInterface::class, function ($app) {
            return new BarcodeGeneratorService($app->make(BarcodeRepositoryInterface::class));
        });

        $this->app->bind(BarcodeScannerServiceInterface::class, function ($app) {
            return new BarcodeScannerService($app->make(BarcodeRepositoryInterface::class));
        });

        $this->app->bind(LabelTemplateServiceInterface::class, function ($app) {
            return new LabelTemplateService($app->make(LabelTemplateRepositoryInterface::class));
        });

        $this->app->bind(BarcodePrinterDispatcherInterface::class, function ($app) {
            return new BarcodePrinterDispatcher(
                labelTemplateService: $app->make(LabelTemplateServiceInterface::class),
                drivers: [
                    new ZplPrinterDriver(),
                    new EplPrinterDriver(),
                    new SvgPrinterDriver(),
                ],
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../../routes/api.php');
    }
}
