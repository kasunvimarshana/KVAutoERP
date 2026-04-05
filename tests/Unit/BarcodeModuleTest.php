<?php declare(strict_types=1);
namespace Tests\Unit;

use Modules\Barcode\Domain\Entities\BarcodeSymbology;
use Modules\Barcode\Domain\Entities\LabelTemplate;
use Modules\Barcode\Domain\Entities\BarcodePrintJob;
use Modules\Barcode\Domain\Events\BarcodeScanRecorded;
use Modules\Barcode\Application\Contracts\BarcodeGeneratorInterface;
use Modules\Barcode\Infrastructure\Generators\BarcodeGeneratorDispatcher;
use Modules\Barcode\Infrastructure\Generators\Drivers\SvgBarcodeDriver;
use Modules\Barcode\Infrastructure\Generators\Drivers\QrCodeDriver;
use Modules\Barcode\Infrastructure\Generators\Drivers\DataMatrixDriver;
use PHPUnit\Framework\TestCase;

class BarcodeModuleTest extends TestCase
{
    public function test_barcode_symbology_supported_types(): void
    {
        $b = new BarcodeSymbology('EAN13', '1234567890123', 200, 80, 'svg');
        $this->assertSame('EAN13', $b->getType());
        $this->assertTrue($b->is1D());
        $this->assertFalse($b->is2D());
    }

    public function test_barcode_symbology_2d(): void
    {
        $b = new BarcodeSymbology('QrCode', 'https://example.com', 100, 100, 'svg');
        $this->assertTrue($b->is2D());
        $this->assertFalse($b->is1D());
    }

    public function test_barcode_symbology_rejects_unsupported(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new BarcodeSymbology('INVALID', 'data', null, null, 'svg');
    }

    public function test_label_template_render(): void
    {
        $tpl = new LabelTemplate(1, 1, 'Price Tag', 'zpl', 'Product: {{ name }} Price: {{ price }}', 100, 50, true);
        $rendered = $tpl->render(['name' => 'Widget', 'price' => '9.99']);
        $this->assertStringContainsString('Widget', $rendered);
        $this->assertStringContainsString('9.99', $rendered);
    }

    public function test_label_template_render_no_spaces(): void
    {
        $tpl = new LabelTemplate(1, 1, 'Tag', 'svg', 'Data:{{value}}', 100, 50, true);
        $rendered = $tpl->render(['value' => 'ABC123']);
        $this->assertStringContainsString('ABC123', $rendered);
    }

    public function test_barcode_print_job_status(): void
    {
        $job = new BarcodePrintJob(1, 1, 1, '123456', 'EAN13', 2, 'completed', null, new \DateTimeImmutable());
        $this->assertTrue($job->isCompleted());
        $this->assertFalse($job->isFailed());
    }

    public function test_barcode_print_job_failed(): void
    {
        $job = new BarcodePrintJob(2, 1, 1, 'bad', 'QrCode', 1, 'failed', 'Printer offline', null);
        $this->assertTrue($job->isFailed());
        $this->assertSame('Printer offline', $job->getErrorMessage());
    }

    public function test_barcode_scan_recorded_event(): void
    {
        $event = new BarcodeScanRecorded('1234567890', 'EAN13', 1, 42, new \DateTimeImmutable());
        $this->assertSame('1234567890', $event->barcodeData);
        $this->assertSame('EAN13', $event->barcodeType);
        $this->assertSame(1, $event->tenantId);
    }

    public function test_svg_barcode_driver_generates(): void
    {
        $driver = new SvgBarcodeDriver();
        $this->assertTrue($driver->supports('EAN13'));
        $this->assertTrue($driver->supports('Code128'));
        $this->assertFalse($driver->supports('QrCode'));
        $svg = $driver->generate('EAN13', '123456789012', []);
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_qrcode_driver_generates(): void
    {
        $driver = new QrCodeDriver();
        $this->assertTrue($driver->supports('QrCode'));
        $this->assertFalse($driver->supports('EAN13'));
        $svg = $driver->generate('QrCode', 'https://example.com', []);
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_datamatrix_driver_generates(): void
    {
        $driver = new DataMatrixDriver();
        $this->assertTrue($driver->supports('DataMatrix'));
        $this->assertTrue($driver->supports('PDF417'));
        $svg = $driver->generate('DataMatrix', 'ABC', []);
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_dispatcher_routes_to_correct_driver(): void
    {
        $dispatcher = new BarcodeGeneratorDispatcher();
        $dispatcher->register(new SvgBarcodeDriver());
        $dispatcher->register(new QrCodeDriver());
        $dispatcher->register(new DataMatrixDriver());

        $ean = $dispatcher->generate('EAN13', '1234567890123', []);
        $this->assertStringContainsString('<svg', $ean);

        $qr = $dispatcher->generate('QrCode', 'test', []);
        $this->assertStringContainsString('<svg', $qr);
    }

    public function test_dispatcher_throws_for_unregistered_type(): void
    {
        $dispatcher = new BarcodeGeneratorDispatcher();
        $this->expectException(\InvalidArgumentException::class);
        $dispatcher->generate('EAN13', '123', []);
    }
}
