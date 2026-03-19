<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\UomRepositoryInterface;
use App\Models\UnitOfMeasure;
use App\Models\UomConversion;
use App\Services\UomService;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Unit tests for UomService.
 *
 * Verifies BCMath precision for conversion factors (6 decimal places)
 * and correct service logic for CRUD and conversion operations.
 */
final class UomServiceTest extends TestCase
{
    private UomRepositoryInterface&MockInterface $repository;
    private UomService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(UomRepositoryInterface::class);
        $this->service    = new UomService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // findOrFail
    // -------------------------------------------------------------------------

    /** @test */
    public function it_returns_uom_when_found(): void
    {
        $uom = $this->makeUom(['id' => 'uom-001', 'name' => 'Kilogram', 'symbol' => 'kg']);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with('uom-001')
            ->andReturn($uom);

        $result = $this->service->findOrFail('uom-001');

        self::assertSame('Kilogram', $result->name);
    }

    /** @test */
    public function it_throws_not_found_when_uom_is_missing(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with('missing')
            ->andReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->findOrFail('missing');
    }

    // -------------------------------------------------------------------------
    // convert — BCMath precision
    // -------------------------------------------------------------------------

    /** @test */
    public function it_converts_an_amount_using_bcmath_with_6_decimal_places(): void
    {
        // 1 kg = 1000 g  →  factor = 1000.000000
        $conversion = $this->makeConversion([
            'from_uom_id' => 'uom-kg',
            'to_uom_id'   => 'uom-g',
            'factor'      => '1000.000000',
        ]);

        $this->repository
            ->shouldReceive('findConversion')
            ->once()
            ->with('uom-kg', 'uom-g')
            ->andReturn($conversion);

        $result = $this->service->convert('uom-kg', 'uom-g', '2.5');

        // 2.5 × 1000.000000 = 2500.000000
        self::assertSame('2500.000000', $result);
    }

    /** @test */
    public function it_handles_fractional_conversion_factors_with_precision(): void
    {
        // 1 g = 0.001 kg  →  factor = 0.001000
        $conversion = $this->makeConversion([
            'from_uom_id' => 'uom-g',
            'to_uom_id'   => 'uom-kg',
            'factor'      => '0.001000',
        ]);

        $this->repository
            ->shouldReceive('findConversion')
            ->once()
            ->with('uom-g', 'uom-kg')
            ->andReturn($conversion);

        $result = $this->service->convert('uom-g', 'uom-kg', '500');

        // 500 × 0.001000 = 0.500000
        self::assertSame('0.500000', $result);
    }

    /** @test */
    public function it_returns_same_value_when_converting_same_uom(): void
    {
        $result = $this->service->convert('uom-kg', 'uom-kg', '3.75');

        // Same UOM: result should equal input normalised to 6 d.p.
        self::assertSame('3.750000', $result);
    }

    /** @test */
    public function it_throws_not_found_when_no_conversion_path_exists(): void
    {
        $this->repository
            ->shouldReceive('findConversion')
            ->once()
            ->with('uom-a', 'uom-b')
            ->andReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->convert('uom-a', 'uom-b', '10');
    }

    /** @test */
    public function it_preserves_6_decimal_places_for_small_floating_point_amounts(): void
    {
        // Simulate a common floating-point pitfall: 0.1 + 0.2 ≠ 0.3 in IEEE 754.
        // BCMath must return exactly 0.300000 when amount = '0.3' and factor = '1.000000'.
        $conversion = $this->makeConversion([
            'from_uom_id' => 'uom-001',
            'to_uom_id'   => 'uom-002',
            'factor'      => '1.000000',
        ]);

        $this->repository
            ->shouldReceive('findConversion')
            ->with('uom-001', 'uom-002')
            ->andReturn($conversion);

        $result = $this->service->convert('uom-001', 'uom-002', '0.3');

        self::assertSame('0.300000', $result);

        // Verify using BCMath directly that IEEE 754 would fail here.
        // floatval('0.1') + floatval('0.2') !== 0.3 in native float arithmetic.
        self::assertNotSame('0.300000', (string) (0.1 + 0.2));
    }

    // -------------------------------------------------------------------------
    // upsertConversion
    // -------------------------------------------------------------------------

    /** @test */
    public function it_creates_forward_and_inverse_conversions(): void
    {
        // Both UOMs must exist.
        $kgUom = $this->makeUom(['id' => 'uom-kg', 'name' => 'Kilogram']);
        $gUom  = $this->makeUom(['id' => 'uom-g',  'name' => 'Gram']);

        $this->repository->shouldReceive('findById')->with('uom-kg')->andReturn($kgUom);
        $this->repository->shouldReceive('findById')->with('uom-g')->andReturn($gUom);

        // No existing forward conversion.
        $this->repository
            ->shouldReceive('findConversion')
            ->with('uom-kg', 'uom-g')
            ->andReturn(null);

        // No existing inverse conversion.
        $this->repository
            ->shouldReceive('findConversion')
            ->with('uom-g', 'uom-kg')
            ->andReturn(null);

        $forwardConversion = $this->makeConversion([
            'from_uom_id' => 'uom-kg',
            'to_uom_id'   => 'uom-g',
            'factor'      => '1000.000000',
        ]);

        $this->repository
            ->shouldReceive('createConversion')
            ->once()
            ->with(Mockery::on(static function (array $data): bool {
                return $data['from_uom_id'] === 'uom-kg'
                    && $data['to_uom_id'] === 'uom-g'
                    && $data['factor'] === '1000.000000';
            }))
            ->andReturn($forwardConversion);

        // Inverse: 1 / 1000 = 0.001000
        $this->repository
            ->shouldReceive('createConversion')
            ->once()
            ->with(Mockery::on(static function (array $data): bool {
                return $data['from_uom_id'] === 'uom-g'
                    && $data['to_uom_id'] === 'uom-kg'
                    && $data['factor'] === '0.001000';
            }))
            ->andReturn($this->makeConversion([
                'from_uom_id' => 'uom-g',
                'to_uom_id'   => 'uom-kg',
                'factor'      => '0.001000',
            ]));

        $result = $this->service->upsertConversion('uom-kg', 'uom-g', '1000');

        self::assertSame('1000.000000', $result->factor);
    }

    /** @test */
    public function it_throws_validation_exception_when_same_uom_given_for_conversion(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->upsertConversion('uom-kg', 'uom-kg', '1.0');
    }

    /** @test */
    public function it_throws_validation_exception_for_zero_conversion_factor(): void
    {
        $kgUom = $this->makeUom(['id' => 'uom-kg', 'name' => 'Kilogram']);
        $gUom  = $this->makeUom(['id' => 'uom-g',  'name' => 'Gram']);

        $this->repository->shouldReceive('findById')->with('uom-kg')->andReturn($kgUom);
        $this->repository->shouldReceive('findById')->with('uom-g')->andReturn($gUom);

        $this->expectException(ValidationException::class);

        $this->service->upsertConversion('uom-kg', 'uom-g', '0');
    }

    /** @test */
    public function it_throws_validation_exception_for_negative_conversion_factor(): void
    {
        $kgUom = $this->makeUom(['id' => 'uom-kg', 'name' => 'Kilogram']);
        $gUom  = $this->makeUom(['id' => 'uom-g',  'name' => 'Gram']);

        $this->repository->shouldReceive('findById')->with('uom-kg')->andReturn($kgUom);
        $this->repository->shouldReceive('findById')->with('uom-g')->andReturn($gUom);

        $this->expectException(ValidationException::class);

        $this->service->upsertConversion('uom-kg', 'uom-g', '-5');
    }

    // -------------------------------------------------------------------------
    // BCMath precision verification (standalone)
    // -------------------------------------------------------------------------

    /** @test */
    public function bcmath_multiplication_does_not_suffer_floating_point_drift(): void
    {
        // Validate that BCMath handles repeating decimal factors correctly.
        // 1/3 ≈ 0.333333 (6 d.p.), so 3 × 0.333333 should be 0.999999 not 1.0.
        $result = bcmul('3', '0.333333', 6);
        self::assertSame('0.999999', $result);

        // Full precision: 3 × (1/3) using bcmath division first.
        // At 10 d.p., 1/3 = 0.3333333333, so 3 × that = 0.9999999999 (expected BCMath truncation).
        $oneThird    = bcdiv('1', '3', 10);
        $backsToFull = bcmul('3', $oneThird, 10);
        self::assertSame('0.9999999999', $backsToFull);
    }

    /** @test */
    public function bcmath_preserves_trailing_zeros_to_scale(): void
    {
        self::assertSame('1.000000', bcadd('1', '0', 6));
        self::assertSame('0.500000', bcdiv('1', '2', 6));
        self::assertSame('25.000000', bcmul('5', '5', 6));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a UnitOfMeasure model with attributes set directly — no DB.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function makeUom(array $attributes): UnitOfMeasure
    {
        $uom = new UnitOfMeasure();

        foreach ($attributes as $key => $value) {
            $uom->setAttribute($key, $value);
        }

        return $uom;
    }

    /**
     * Build a UomConversion model with attributes set directly — no DB.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function makeConversion(array $attributes): UomConversion
    {
        $conv = new UomConversion();

        foreach ($attributes as $key => $value) {
            $conv->setAttribute($key, $value);
        }

        return $conv;
    }
}
