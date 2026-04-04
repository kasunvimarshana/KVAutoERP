<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Modules\Barcode\Domain\ValueObjects\BarcodeType;
use Modules\Barcode\Domain\ValueObjects\BarcodeOutputFormat;
use Modules\Barcode\Domain\Entities\BarcodeDefinition;
use Modules\Barcode\Domain\Entities\BarcodeScan;
use Modules\Barcode\Domain\Exceptions\BarcodeNotFoundException;
use Modules\Barcode\Domain\Exceptions\InvalidBarcodeException;
use Modules\Barcode\Domain\Exceptions\UnsupportedBarcodeTypeException;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodeDefinitionRepositoryInterface;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodeScanRepositoryInterface;
use Modules\Barcode\Application\Services\ManageBarcodeService;
use Modules\Barcode\Application\Services\RecordBarcodeScanService;
use Modules\Barcode\Application\Services\GenerateBarcodeService;
use Modules\Barcode\Infrastructure\Generators\BarcodeGeneratorDispatcher;
use Modules\Barcode\Infrastructure\Generators\BarcodeGeneratorDriverInterface;
use Modules\Barcode\Infrastructure\Generators\Drivers\Code128Driver;
use Modules\Barcode\Infrastructure\Generators\Drivers\Code39Driver;
use Modules\Barcode\Infrastructure\Generators\Drivers\Ean13Driver;
use Modules\Barcode\Infrastructure\Generators\Drivers\Ean8Driver;
use Modules\Barcode\Infrastructure\Generators\Drivers\QrCodeDriver;

class BarcodeModuleTest extends TestCase
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helper factories
    // ─────────────────────────────────────────────────────────────────────────

    private function makeDefinition(
        ?int    $id        = 1,
        int     $tenantId  = 1,
        string  $type      = BarcodeType::CODE128,
        string  $value     = 'TEST123',
        ?string $label     = 'Test Label',
        bool    $isActive  = true,
    ): BarcodeDefinition {
        return new BarcodeDefinition(
            $id,
            $tenantId,
            $type,
            $value,
            $label,
            null,
            null,
            [],
            $isActive,
            new \DateTime(),
            new \DateTime(),
        );
    }

    private function makeScan(
        ?int    $id                  = 1,
        int     $tenantId            = 1,
        ?int    $barcodeDefinitionId = 5,
        string  $scannedValue        = 'TEST123',
        ?string $resolvedType        = BarcodeType::CODE128,
    ): BarcodeScan {
        return new BarcodeScan(
            $id,
            $tenantId,
            $barcodeDefinitionId,
            $scannedValue,
            $resolvedType,
            42,
            'device-001',
            'warehouse-A',
            ['source' => 'mobile'],
            new \DateTime(),
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BarcodeType value object
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_barcode_type_from_string_valid_types(): void
    {
        $validTypes = [
            BarcodeType::CODE128,
            BarcodeType::CODE39,
            BarcodeType::CODE93,
            BarcodeType::EAN13,
            BarcodeType::EAN8,
            BarcodeType::UPCA,
            BarcodeType::UPCE,
            BarcodeType::ITF14,
            BarcodeType::CODABAR,
            BarcodeType::MSI,
            BarcodeType::INTERLEAVED2OF5,
            BarcodeType::QR,
            BarcodeType::DATAMATRIX,
            BarcodeType::PDF417,
            BarcodeType::AZTEC,
        ];

        foreach ($validTypes as $typeStr) {
            $type = BarcodeType::fromString($typeStr);
            $this->assertSame($typeStr, $type->getValue());
        }
    }

    /** @test */
    public function test_barcode_type_from_string_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BarcodeType::fromString('INVALID_TYPE');
    }

    /** @test */
    public function test_barcode_type_is_one_dimensional(): void
    {
        $oneDTypes = [
            BarcodeType::CODE128,
            BarcodeType::CODE39,
            BarcodeType::CODE93,
            BarcodeType::EAN13,
            BarcodeType::EAN8,
            BarcodeType::UPCA,
            BarcodeType::UPCE,
            BarcodeType::ITF14,
            BarcodeType::CODABAR,
            BarcodeType::MSI,
            BarcodeType::INTERLEAVED2OF5,
        ];

        foreach ($oneDTypes as $typeStr) {
            $type = BarcodeType::fromString($typeStr);
            $this->assertTrue($type->isOneDimensional(), "{$typeStr} should be 1-D");
            $this->assertFalse($type->isTwoDimensional(), "{$typeStr} should not be 2-D");
        }
    }

    /** @test */
    public function test_barcode_type_is_two_dimensional(): void
    {
        $twoDTypes = [
            BarcodeType::QR,
            BarcodeType::DATAMATRIX,
            BarcodeType::PDF417,
            BarcodeType::AZTEC,
        ];

        foreach ($twoDTypes as $typeStr) {
            $type = BarcodeType::fromString($typeStr);
            $this->assertTrue($type->isTwoDimensional(), "{$typeStr} should be 2-D");
            $this->assertFalse($type->isOneDimensional(), "{$typeStr} should not be 1-D");
        }
    }

    /** @test */
    public function test_barcode_type_all_types_returns_15_types(): void
    {
        $this->assertCount(15, BarcodeType::allTypes());
    }

    /** @test */
    public function test_barcode_type_get_value_returns_string(): void
    {
        $type = BarcodeType::code128();
        $this->assertSame(BarcodeType::CODE128, $type->getValue());

        $type = BarcodeType::qr();
        $this->assertSame(BarcodeType::QR, $type->getValue());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BarcodeOutputFormat value object
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_output_format_from_string_valid(): void
    {
        $svg    = BarcodeOutputFormat::fromString(BarcodeOutputFormat::SVG);
        $png    = BarcodeOutputFormat::fromString(BarcodeOutputFormat::PNG_BASE64);
        $raw    = BarcodeOutputFormat::fromString(BarcodeOutputFormat::RAW);

        $this->assertSame(BarcodeOutputFormat::SVG,        $svg->getValue());
        $this->assertSame(BarcodeOutputFormat::PNG_BASE64, $png->getValue());
        $this->assertSame(BarcodeOutputFormat::RAW,        $raw->getValue());
    }

    /** @test */
    public function test_output_format_from_string_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BarcodeOutputFormat::fromString('pdf');
    }

    /** @test */
    public function test_output_format_get_value(): void
    {
        $format = BarcodeOutputFormat::svg();
        $this->assertSame('svg', $format->getValue());

        $format = BarcodeOutputFormat::pngBase64();
        $this->assertSame('png_base64', $format->getValue());

        $format = BarcodeOutputFormat::raw();
        $this->assertSame('raw', $format->getValue());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BarcodeDefinition entity
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_barcode_definition_creation(): void
    {
        $def = $this->makeDefinition(id: 7, tenantId: 3, type: BarcodeType::QR, value: 'QR-001');

        $this->assertSame(7,                $def->getId());
        $this->assertSame(3,                $def->getTenantId());
        $this->assertSame(BarcodeType::QR,  $def->getType());
        $this->assertSame('QR-001',         $def->getValue());
        $this->assertTrue($def->isActive());
    }

    /** @test */
    public function test_barcode_definition_activate(): void
    {
        $def = $this->makeDefinition(isActive: false);
        $this->assertFalse($def->isActive());

        $def->activate();

        $this->assertTrue($def->isActive());
        $this->assertNotNull($def->getUpdatedAt());
    }

    /** @test */
    public function test_barcode_definition_deactivate(): void
    {
        $def = $this->makeDefinition(isActive: true);
        $this->assertTrue($def->isActive());

        $def->deactivate();

        $this->assertFalse($def->isActive());
        $this->assertNotNull($def->getUpdatedAt());
    }

    /** @test */
    public function test_barcode_definition_update_label(): void
    {
        $def = $this->makeDefinition(label: 'Old Label');
        $def->updateLabel('New Label');

        $this->assertSame('New Label', $def->getLabel());
        $this->assertNotNull($def->getUpdatedAt());
    }

    /** @test */
    public function test_barcode_definition_update_metadata(): void
    {
        $def = $this->makeDefinition();
        $def->updateMetadata(['sku' => 'ABC-123', 'qty' => 10]);

        $this->assertSame(['sku' => 'ABC-123', 'qty' => 10], $def->getMetadata());
        $this->assertNotNull($def->getUpdatedAt());
    }

    /** @test */
    public function test_barcode_definition_getters(): void
    {
        $createdAt = new \DateTime('2024-01-01');
        $updatedAt = new \DateTime('2024-06-01');

        $def = new BarcodeDefinition(
            42,
            5,
            BarcodeType::EAN13,
            '5901234123457',
            'Product EAN',
            'product',
            99,
            ['color' => 'red'],
            true,
            $createdAt,
            $updatedAt,
        );

        $this->assertSame(42,                  $def->getId());
        $this->assertSame(5,                   $def->getTenantId());
        $this->assertSame(BarcodeType::EAN13,  $def->getType());
        $this->assertSame('5901234123457',     $def->getValue());
        $this->assertSame('Product EAN',       $def->getLabel());
        $this->assertSame('product',           $def->getEntityType());
        $this->assertSame(99,                  $def->getEntityId());
        $this->assertSame(['color' => 'red'],  $def->getMetadata());
        $this->assertTrue($def->isActive());
        $this->assertSame($createdAt,          $def->getCreatedAt());
        $this->assertSame($updatedAt,          $def->getUpdatedAt());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BarcodeScan entity
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_barcode_scan_creation(): void
    {
        $scan = $this->makeScan();

        $this->assertSame(1,                    $scan->getId());
        $this->assertSame(1,                    $scan->getTenantId());
        $this->assertSame(5,                    $scan->getBarcodeDefinitionId());
        $this->assertSame('TEST123',            $scan->getScannedValue());
        $this->assertSame(BarcodeType::CODE128, $scan->getResolvedType());
    }

    /** @test */
    public function test_barcode_scan_getters(): void
    {
        $scannedAt = new \DateTime('2024-03-15 10:30:00');

        $scan = new BarcodeScan(
            11,
            2,
            7,
            'SCAN-VALUE',
            BarcodeType::QR,
            99,
            'scanner-A',
            'dock-1',
            ['ref' => 'PO-001'],
            $scannedAt,
        );

        $this->assertSame(11,               $scan->getId());
        $this->assertSame(2,                $scan->getTenantId());
        $this->assertSame(7,                $scan->getBarcodeDefinitionId());
        $this->assertSame('SCAN-VALUE',     $scan->getScannedValue());
        $this->assertSame(BarcodeType::QR,  $scan->getResolvedType());
        $this->assertSame(99,               $scan->getScannedByUserId());
        $this->assertSame('scanner-A',      $scan->getDeviceId());
        $this->assertSame('dock-1',         $scan->getLocationTag());
        $this->assertSame(['ref' => 'PO-001'], $scan->getMetadata());
        $this->assertSame($scannedAt,       $scan->getScannedAt());
    }

    /** @test */
    public function test_barcode_scan_null_optional_fields(): void
    {
        $scan = new BarcodeScan(
            null,
            1,
            null,
            'UNKNOWN',
            null,
            null,
            null,
            null,
            [],
            new \DateTime(),
        );

        $this->assertNull($scan->getId());
        $this->assertNull($scan->getBarcodeDefinitionId());
        $this->assertNull($scan->getResolvedType());
        $this->assertNull($scan->getScannedByUserId());
        $this->assertNull($scan->getDeviceId());
        $this->assertNull($scan->getLocationTag());
        $this->assertSame([], $scan->getMetadata());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Exceptions
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_barcode_not_found_exception_with_id(): void
    {
        $e = BarcodeNotFoundException::withId(42);

        $this->assertInstanceOf(\RuntimeException::class, $e);
        $this->assertSame(404, $e->getCode());
        $this->assertStringContainsString('42', $e->getMessage());
    }

    /** @test */
    public function test_invalid_barcode_exception_for_value(): void
    {
        $e = InvalidBarcodeException::forValue('BAD!', BarcodeType::CODE39, 'unsupported character');

        $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        $this->assertSame(422, $e->getCode());
        $this->assertStringContainsString('BAD!',              $e->getMessage());
        $this->assertStringContainsString(BarcodeType::CODE39, $e->getMessage());
        $this->assertStringContainsString('unsupported character', $e->getMessage());
    }

    /** @test */
    public function test_unsupported_type_exception_for_type(): void
    {
        $e = UnsupportedBarcodeTypeException::forType('FANCY_TYPE');

        $this->assertInstanceOf(\RuntimeException::class, $e);
        $this->assertSame(422, $e->getCode());
        $this->assertStringContainsString('FANCY_TYPE', $e->getMessage());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BarcodeGeneratorDispatcher
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_dispatcher_add_and_has_driver(): void
    {
        $dispatcher = new BarcodeGeneratorDispatcher();
        $this->assertFalse($dispatcher->hasDriver(BarcodeType::CODE128));

        /** @var BarcodeGeneratorDriverInterface&MockObject $driver */
        $driver = $this->createMock(BarcodeGeneratorDriverInterface::class);
        $dispatcher->addDriver(BarcodeType::CODE128, $driver);

        $this->assertTrue($dispatcher->hasDriver(BarcodeType::CODE128));
    }

    /** @test */
    public function test_dispatcher_get_supported_types(): void
    {
        $dispatcher = new BarcodeGeneratorDispatcher();

        /** @var BarcodeGeneratorDriverInterface&MockObject $d1 */
        $d1 = $this->createMock(BarcodeGeneratorDriverInterface::class);
        /** @var BarcodeGeneratorDriverInterface&MockObject $d2 */
        $d2 = $this->createMock(BarcodeGeneratorDriverInterface::class);

        $dispatcher->addDriver(BarcodeType::CODE128, $d1);
        $dispatcher->addDriver(BarcodeType::QR,      $d2);

        $types = $dispatcher->getSupportedTypes();
        $this->assertContains(BarcodeType::CODE128, $types);
        $this->assertContains(BarcodeType::QR,      $types);
        $this->assertCount(2, $types);
    }

    /** @test */
    public function test_dispatcher_throws_for_unknown_type(): void
    {
        $dispatcher = new BarcodeGeneratorDispatcher();
        $def        = $this->makeDefinition(type: BarcodeType::CODE128);

        $this->expectException(UnsupportedBarcodeTypeException::class);
        $dispatcher->generate($def, BarcodeOutputFormat::SVG, []);
    }

    /** @test */
    public function test_dispatcher_validate_delegates_to_driver(): void
    {
        $dispatcher = new BarcodeGeneratorDispatcher();

        /** @var BarcodeGeneratorDriverInterface&MockObject $driver */
        $driver = $this->createMock(BarcodeGeneratorDriverInterface::class);
        $driver->expects($this->once())
               ->method('validate')
               ->with('HELLO')
               ->willReturn(true);

        $dispatcher->addDriver(BarcodeType::CODE128, $driver);

        $this->assertTrue($dispatcher->validate(BarcodeType::CODE128, 'HELLO'));
    }

    /** @test */
    public function test_dispatcher_generate_returns_string(): void
    {
        $dispatcher = new BarcodeGeneratorDispatcher();
        $def        = $this->makeDefinition(type: BarcodeType::CODE128, value: 'HELLO');

        /** @var BarcodeGeneratorDriverInterface&MockObject $driver */
        $driver = $this->createMock(BarcodeGeneratorDriverInterface::class);
        $driver->expects($this->once())
               ->method('generate')
               ->with('HELLO', BarcodeOutputFormat::SVG, [])
               ->willReturn('<svg/>');

        $dispatcher->addDriver(BarcodeType::CODE128, $driver);

        $result = $dispatcher->generate($def, BarcodeOutputFormat::SVG, []);
        $this->assertSame('<svg/>', $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Code128Driver
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_code128_supports_correct_type(): void
    {
        $driver = new Code128Driver();
        $this->assertTrue($driver->supports(BarcodeType::CODE128));
        $this->assertFalse($driver->supports(BarcodeType::CODE39));
        $this->assertFalse($driver->supports(BarcodeType::QR));
    }

    /** @test */
    public function test_code128_generate_returns_svg(): void
    {
        $driver = new Code128Driver();
        $svg    = $driver->generate('ABC', BarcodeOutputFormat::SVG, []);
        $this->assertIsString($svg);
        $this->assertNotEmpty($svg);
    }

    /** @test */
    public function test_code128_svg_contains_svg_tag(): void
    {
        $driver = new Code128Driver();
        $svg    = $driver->generate('TEST', BarcodeOutputFormat::SVG, []);
        $this->assertStringContainsString('<svg', $svg);
    }

    /** @test */
    public function test_code128_validate_valid_string(): void
    {
        $driver = new Code128Driver();
        $this->assertTrue($driver->validate('Hello World 123'));
        $this->assertTrue($driver->validate('ABC'));
        $this->assertTrue($driver->validate('0123456789'));
    }

    /** @test */
    public function test_code128_validate_rejects_invalid_chars(): void
    {
        $driver = new Code128Driver();
        $this->assertFalse($driver->validate(''));
        $this->assertFalse($driver->validate("\x01 control")); // ASCII < 32
    }

    /** @test */
    public function test_code128_generate_hello_world(): void
    {
        $driver = new Code128Driver();
        $svg    = $driver->generate('Hello World', BarcodeOutputFormat::SVG, []);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('Hello World', $svg);
    }

    /** @test */
    public function test_code128_generate_different_values_produce_different_svgs(): void
    {
        $driver = new Code128Driver();
        $svg1   = $driver->generate('AAAA', BarcodeOutputFormat::SVG, []);
        $svg2   = $driver->generate('BBBB', BarcodeOutputFormat::SVG, []);
        $this->assertNotSame($svg1, $svg2);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Code39Driver
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_code39_supports_correct_type(): void
    {
        $driver = new Code39Driver();
        $this->assertTrue($driver->supports(BarcodeType::CODE39));
        $this->assertFalse($driver->supports(BarcodeType::CODE128));
        $this->assertFalse($driver->supports(BarcodeType::QR));
    }

    /** @test */
    public function test_code39_generate_returns_svg(): void
    {
        $driver = new Code39Driver();
        $svg    = $driver->generate('ABC123', BarcodeOutputFormat::SVG, []);
        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
    }

    /** @test */
    public function test_code39_validate_valid_chars(): void
    {
        $driver = new Code39Driver();
        $this->assertTrue($driver->validate('HELLO'));
        $this->assertTrue($driver->validate('123'));
        $this->assertTrue($driver->validate('ABC-123'));
        $this->assertFalse($driver->validate(''));
        $this->assertFalse($driver->validate('HELLO*WORLD')); // * is reserved
        $this->assertTrue($driver->validate('hello'));        // lowercase is uppercased internally
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Ean13Driver
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_ean13_supports_correct_type(): void
    {
        $driver = new Ean13Driver();
        $this->assertTrue($driver->supports(BarcodeType::EAN13));
        $this->assertFalse($driver->supports(BarcodeType::EAN8));
        $this->assertFalse($driver->supports(BarcodeType::CODE128));
    }

    /** @test */
    public function test_ean13_generate_returns_svg(): void
    {
        $driver = new Ean13Driver();
        // 5901234123457 is a valid EAN-13
        $svg    = $driver->generate('5901234123457', BarcodeOutputFormat::SVG, []);
        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
    }

    /** @test */
    public function test_ean13_validate_valid_13_digit(): void
    {
        $driver = new Ean13Driver();
        $this->assertTrue($driver->validate('5901234123457')); // valid EAN-13 with correct check digit
        $this->assertTrue($driver->validate('590123412345'));  // 12 digits, check digit auto-computed
    }

    /** @test */
    public function test_ean13_validate_invalid_length(): void
    {
        $driver = new Ean13Driver();
        $this->assertFalse($driver->validate('123'));            // too short
        $this->assertFalse($driver->validate('12345678901234')); // too long
        $this->assertFalse($driver->validate('590123412345X'));  // non-digit
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Ean8Driver
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_ean8_supports_correct_type(): void
    {
        $driver = new Ean8Driver();
        $this->assertTrue($driver->supports(BarcodeType::EAN8));
        $this->assertFalse($driver->supports(BarcodeType::EAN13));
        $this->assertFalse($driver->supports(BarcodeType::CODE128));
    }

    /** @test */
    public function test_ean8_generate_returns_svg(): void
    {
        $driver = new Ean8Driver();
        // 7 digits – check digit will be auto-appended
        $svg    = $driver->generate('1234567', BarcodeOutputFormat::SVG, []);
        $this->assertIsString($svg);
        $this->assertStringContainsString('<svg', $svg);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // QrCodeDriver
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_qr_supports_correct_type(): void
    {
        $driver = new QrCodeDriver();
        $this->assertTrue($driver->supports(BarcodeType::QR));
        $this->assertFalse($driver->supports(BarcodeType::CODE128));
        $this->assertFalse($driver->supports(BarcodeType::EAN13));
    }

    /** @test */
    public function test_qr_generate_returns_svg(): void
    {
        $driver = new QrCodeDriver();
        $svg    = $driver->generate('Hello', BarcodeOutputFormat::SVG, []);
        $this->assertIsString($svg);
        $this->assertNotEmpty($svg);
    }

    /** @test */
    public function test_qr_svg_contains_svg_tag(): void
    {
        $driver = new QrCodeDriver();
        $svg    = $driver->generate('Hello', BarcodeOutputFormat::SVG, []);
        $this->assertStringContainsString('<svg', $svg);
    }

    /** @test */
    public function test_qr_generate_short_value(): void
    {
        $driver = new QrCodeDriver();
        $svg    = $driver->generate('A', BarcodeOutputFormat::SVG, []);
        $this->assertStringContainsString('<svg', $svg);
    }

    /** @test */
    public function test_qr_generate_long_value(): void
    {
        $driver = new QrCodeDriver();
        $value  = str_repeat('Hello World! ', 10); // ~130 chars
        $svg    = $driver->generate($value, BarcodeOutputFormat::SVG, []);
        $this->assertStringContainsString('<svg', $svg);
    }

    /** @test */
    public function test_qr_generate_numeric_value(): void
    {
        $driver = new QrCodeDriver();
        $svg    = $driver->generate('1234567890', BarcodeOutputFormat::SVG, []);
        $this->assertStringContainsString('<svg', $svg);
    }

    /** @test */
    public function test_qr_generate_url(): void
    {
        $driver = new QrCodeDriver();
        $svg    = $driver->generate('https://example.com/path?query=1', BarcodeOutputFormat::SVG, []);
        $this->assertStringContainsString('<svg', $svg);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ManageBarcodeService
    // ─────────────────────────────────────────────────────────────────────────

    private function makeDefinitionRepo(): BarcodeDefinitionRepositoryInterface&MockObject
    {
        return $this->createMock(BarcodeDefinitionRepositoryInterface::class);
    }

    /** @test */
    public function test_manage_service_create_barcode(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);

        $saved = $this->makeDefinition(id: 10);

        $repo->expects($this->once())
             ->method('save')
             ->willReturn($saved);

        $result = $service->create(1, BarcodeType::CODE128, 'ABC', 'My Label', null, null);

        $this->assertSame($saved, $result);
    }

    /** @test */
    public function test_manage_service_create_invalid_type_throws(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);

        $this->expectException(\InvalidArgumentException::class);
        $service->create(1, 'BOGUS_TYPE', 'value', null, null, null);
    }

    /** @test */
    public function test_manage_service_get_by_id_found(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);
        $def     = $this->makeDefinition(id: 5);

        $repo->expects($this->once())
             ->method('findById')
             ->with(5)
             ->willReturn($def);

        $result = $service->getById(5);
        $this->assertSame($def, $result);
    }

    /** @test */
    public function test_manage_service_get_by_id_not_found_throws(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);

        $repo->method('findById')->willReturn(null);

        $this->expectException(BarcodeNotFoundException::class);
        $service->getById(999);
    }

    /** @test */
    public function test_manage_service_get_by_value_not_found_throws(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);

        $repo->method('findByValue')->willReturn(null);

        $this->expectException(BarcodeNotFoundException::class);
        $service->getByValue(1, 'NONEXISTENT');
    }

    /** @test */
    public function test_manage_service_activate(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);
        $def     = $this->makeDefinition(id: 3, isActive: false);

        $repo->method('findById')->willReturn($def);
        $repo->expects($this->once())
             ->method('save')
             ->with($this->callback(fn ($d) => $d->isActive()))
             ->willReturn($def);

        $service->activate(3);
        $this->assertTrue($def->isActive());
    }

    /** @test */
    public function test_manage_service_deactivate(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);
        $def     = $this->makeDefinition(id: 4, isActive: true);

        $repo->method('findById')->willReturn($def);
        $repo->expects($this->once())
             ->method('save')
             ->with($this->callback(fn ($d) => !$d->isActive()))
             ->willReturn($def);

        $service->deactivate(4);
        $this->assertFalse($def->isActive());
    }

    /** @test */
    public function test_manage_service_delete(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);
        $def     = $this->makeDefinition(id: 6);

        $repo->method('findById')->willReturn($def);
        $repo->expects($this->once())
             ->method('delete')
             ->with(6);

        $service->delete(6);
    }

    /** @test */
    public function test_manage_service_list_all(): void
    {
        $repo    = $this->makeDefinitionRepo();
        $service = new ManageBarcodeService($repo);
        $list    = [$this->makeDefinition(id: 1), $this->makeDefinition(id: 2)];

        $repo->expects($this->once())
             ->method('findAll')
             ->with(1)
             ->willReturn($list);

        $result = $service->listAll(1);
        $this->assertSame($list, $result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RecordBarcodeScanService
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_record_scan_with_known_barcode(): void
    {
        /** @var BarcodeScanRepositoryInterface&MockObject $scanRepo */
        $scanRepo = $this->createMock(BarcodeScanRepositoryInterface::class);
        $defRepo  = $this->makeDefinitionRepo();
        $service  = new RecordBarcodeScanService($scanRepo, $defRepo);

        $def      = $this->makeDefinition(id: 5, type: BarcodeType::CODE128, value: 'KNOWN');
        $savedScan = $this->makeScan(id: 1, barcodeDefinitionId: 5, resolvedType: BarcodeType::CODE128);

        $defRepo->method('findByValue')->with(1, 'KNOWN')->willReturn($def);
        $scanRepo->expects($this->once())
                 ->method('save')
                 ->willReturn($savedScan);

        $result = $service->record(1, 'KNOWN', 10, 'device-1', null);

        $this->assertSame($savedScan, $result);
    }

    /** @test */
    public function test_record_scan_with_unknown_barcode(): void
    {
        /** @var BarcodeScanRepositoryInterface&MockObject $scanRepo */
        $scanRepo = $this->createMock(BarcodeScanRepositoryInterface::class);
        $defRepo  = $this->makeDefinitionRepo();
        $service  = new RecordBarcodeScanService($scanRepo, $defRepo);

        $savedScan = $this->makeScan(id: 2, barcodeDefinitionId: null, resolvedType: null);

        $defRepo->method('findByValue')->willReturn(null);
        $scanRepo->expects($this->once())
                 ->method('save')
                 ->willReturn($savedScan);

        $result = $service->record(1, 'UNKNOWN_VALUE', null, null, null);

        $this->assertSame($savedScan, $result);
        $this->assertNull($result->getBarcodeDefinitionId());
    }

    /** @test */
    public function test_record_scan_get_by_id_not_found_throws(): void
    {
        /** @var BarcodeScanRepositoryInterface&MockObject $scanRepo */
        $scanRepo = $this->createMock(BarcodeScanRepositoryInterface::class);
        $defRepo  = $this->makeDefinitionRepo();
        $service  = new RecordBarcodeScanService($scanRepo, $defRepo);

        $scanRepo->method('findById')->willReturn(null);

        $this->expectException(BarcodeNotFoundException::class);
        $service->getById(99);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GenerateBarcodeService
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_generate_service_delegates_to_dispatcher(): void
    {
        $dispatcher = new BarcodeGeneratorDispatcher();
        $service    = new GenerateBarcodeService($dispatcher);
        $def        = $this->makeDefinition(type: BarcodeType::CODE128, value: 'HELLO');

        /** @var BarcodeGeneratorDriverInterface&MockObject $driver */
        $driver = $this->createMock(BarcodeGeneratorDriverInterface::class);
        $driver->expects($this->once())
               ->method('generate')
               ->with('HELLO', BarcodeOutputFormat::SVG, [])
               ->willReturn('<svg>barcode</svg>');

        $dispatcher->addDriver(BarcodeType::CODE128, $driver);

        $result = $service->generate($def, BarcodeOutputFormat::SVG, []);
        $this->assertSame('<svg>barcode</svg>', $result);
    }
}
