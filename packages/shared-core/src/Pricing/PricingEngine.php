<?php

namespace Shared\Core\Pricing;

use Shared\Core\Rules\RuleEvaluator;

class PricingEngine
{
    protected $ruleEvaluator;

    public function __construct(RuleEvaluator $ruleEvaluator)
    {
        $this->ruleEvaluator = $ruleEvaluator;
    }

    /**
     * Calculates price based on a set of rules and models.
     */
    public function calculate(float $basePrice, array $rules, array $context): float
    {
        $finalPrice = $basePrice;

        foreach ($rules as $rule) {
            if ($this->ruleEvaluator->evaluate($rule['expression'], $context)) {
                $finalPrice = $this->applyRule($finalPrice, $rule);
            }
        }

        return round($finalPrice, 8); // Numeric(24,8) precision
    }

    protected function applyRule(float $price, array $rule): float
    {
        switch ($rule['type']) {
            case 'flat':
                return $price + $rule['value'];
            case 'percentage':
                return $price * (1 + ($rule['value'] / 100));
            case 'tiered':
                return $this->applyTiered($price, $rule['tiers'], $rule['context_value']);
            default:
                return $price;
        }
    }

    protected function applyTiered(float $price, array $tiers, float $quantity): float
    {
        foreach ($tiers as $tier) {
            if ($quantity >= $tier['min'] && (is_null($tier['max']) || $quantity <= $tier['max'])) {
                return $price * (1 - ($tier['discount'] / 100));
            }
        }
        return $price;
    }
}
