<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSaga extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'status',
        'current_step',
        'steps',
        'context',
    ];

    protected $casts = [
        'steps'   => 'array',
        'context' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markStepCompleted(string $stepName): void
    {
        $steps = $this->steps ?? [];

        foreach ($steps as &$step) {
            if ($step['name'] === $stepName) {
                $step['status']      = 'completed';
                $step['executed_at'] = now()->toIso8601String();
                break;
            }
        }

        $this->steps = $steps;
        $this->save();
    }

    public function markStepFailed(string $stepName, string $error): void
    {
        $steps = $this->steps ?? [];

        foreach ($steps as &$step) {
            if ($step['name'] === $stepName) {
                $step['status'] = 'failed';
                $step['error']  = $error;
                break;
            }
        }

        $this->steps = $steps;
        $this->save();
    }

    public function markStepCompensated(string $stepName): void
    {
        $steps = $this->steps ?? [];

        foreach ($steps as &$step) {
            if ($step['name'] === $stepName) {
                $step['status']          = 'compensated';
                $step['compensated_at']  = now()->toIso8601String();
                break;
            }
        }

        $this->steps = $steps;
        $this->save();
    }

    public function isStepExecuted(string $stepName): bool
    {
        foreach ($this->steps ?? [] as $step) {
            if ($step['name'] === $stepName && $step['status'] === 'completed') {
                return true;
            }
        }

        return false;
    }
}
