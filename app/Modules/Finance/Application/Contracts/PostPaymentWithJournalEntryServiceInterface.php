<?php

declare(strict_types=1);

namespace Modules\Finance\Application\Contracts;

use Modules\Core\Application\Contracts\ServiceInterface;

/**
 * Atomically creates a Journal Entry and posts the associated Payment in one transaction.
 *
 * Expected $data keys:
 *   - 'payment_id'  (int)   — ID of the draft payment to post
 *   - 'journal_entry' (array) — Journal entry creation payload (same shape as CreateJournalEntryService)
 *
 * Returns an array: ['journal_entry' => JournalEntry, 'payment' => Payment]
 *
 * @method array execute(array $data = [])
 */
interface PostPaymentWithJournalEntryServiceInterface extends ServiceInterface {}
