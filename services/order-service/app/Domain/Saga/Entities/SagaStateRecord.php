<?php

namespace App\Domain\Saga\Entities;

use Illuminate\Database\Eloquent\Model;

class SagaStateRecord extends Model
{
    protected $table = 'saga_states';

    protected $fillable = [
        'saga_id',
        'status',
        'payload',
        'context',
        'completed_steps',
        'compensated_steps',
        'events',
        'failure_reason',
    ];

    protected $casts = [
        'payload'           => 'array',
        'context'           => 'array',
        'completed_steps'   => 'array',
        'compensated_steps' => 'array',
        'events'            => 'array',
    ];

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCompensated(): bool
    {
        return $this->status === 'compensated';
    }
}
