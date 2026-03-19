<?php

namespace Shared\Core\Financial;

class HighPrecisionCalculator
{
    /**
     * Precision for all financial calculations.
     */
    protected const PRECISION = 8;

    /**
     * Add two values with high precision.
     */
    public static function add(string $a, string $b): string
    {
        return bcadd($a, $b, self::PRECISION);
    }

    /**
     * Subtract two values with high precision.
     */
    public static function sub(string $a, string $b): string
    {
        return bcsub($a, $b, self::PRECISION);
    }

    /**
     * Multiply two values with high precision.
     */
    public static function mul(string $a, string $b): string
    {
        return bcmul($a, $b, self::PRECISION);
    }

    /**
     * Divide two values with high precision.
     */
    public static function div(string $a, string $b): string
    {
        if (bccomp($b, '0', self::PRECISION) === 0) {
            throw new \InvalidArgumentException("Division by zero.");
        }
        return bcdiv($a, $b, self::PRECISION);
    }

    /**
     * Round a value to the specified precision.
     */
    public static function round(string $value, int $precision = 2): string
    {
        return number_format((float)$value, $precision, '.', '');
    }
}
