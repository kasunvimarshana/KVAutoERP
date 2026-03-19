<?php

namespace Shared\Core\Rules;

use Illuminate\Support\Facades\Log;

class RuleEvaluator
{
    /**
     * Evaluates a rule expression against a context of data.
     * Supports basic operators and variable replacement.
     */
    public function evaluate(string $expression, array $context): bool
    {
        // Simple evaluator: in production, use Symfony/ExpressionLanguage
        // For this demo, we'll replace variables and evaluate.
        $processedExpression = $expression;

        foreach ($context as $key => $value) {
            $processedExpression = str_replace('$' . $key, var_export($value, true), $processedExpression);
        }

        try {
            // Using eval with extreme caution for metadata-driven logic.
            // In a real system, a safe expression parser is mandatory.
            return (bool) eval("return ($processedExpression);");
        } catch (\Throwable $e) {
            Log::error("Rule evaluation failed: {$expression}", ['error' => $e->getMessage()]);
            return false;
        }
    }
}
