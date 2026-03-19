<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Repositories\TokenRevocationRepositoryInterface;
use App\Repositories\SessionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Cleans up expired sessions, token revocations, and stale audit entries.
 * Should be scheduled to run periodically (e.g., daily).
 */
class CleanupExpiredAuthData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(
        SessionRepository $sessionRepository,
        TokenRevocationRepositoryInterface $revocationRepository,
    ): void {
        $deletedSessions = $sessionRepository->cleanupExpired();
        $deletedRevocations = $revocationRepository->cleanupExpired();

        Log::info('Auth cleanup completed', [
            'deleted_sessions'     => $deletedSessions,
            'deleted_revocations'  => $deletedRevocations,
        ]);
    }
}
