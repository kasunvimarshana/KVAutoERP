<?php

declare(strict_types=1);

namespace App\Modules\Webhook\Domain\Models;

use App\Core\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Webhook model.
 *
 * Stores webhook subscription endpoints per tenant.
 *
 * @property int         $id
 * @property int         $tenant_id
 * @property string      $url         Target URL to POST events to
 * @property array       $events      List of subscribed event names
 * @property string|null $secret      HMAC signing secret
 * @property bool        $is_active
 */
class Webhook extends Model
{
    use HasTenant;
    use SoftDeletes;

    protected $table = 'webhooks';

    protected $fillable = ['tenant_id', 'url', 'events', 'secret', 'is_active'];

    protected $casts = [
        'events'    => 'array',
        'is_active' => 'boolean',
    ];
}
