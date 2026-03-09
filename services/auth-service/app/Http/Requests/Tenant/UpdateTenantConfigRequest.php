<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Tenant Configuration Request.
 */
class UpdateTenantConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'configuration'                     => ['required', 'array'],
            'configuration.database'            => ['sometimes', 'array'],
            'configuration.database.host'       => ['sometimes', 'string'],
            'configuration.database.port'       => ['sometimes', 'integer'],
            'configuration.database.username'   => ['sometimes', 'string'],
            'configuration.database.password'   => ['sometimes', 'string'],
            'configuration.mail'                => ['sometimes', 'array'],
            'configuration.mail.driver'         => ['sometimes', 'string', 'in:smtp,mailgun,ses,sendgrid'],
            'configuration.mail.host'           => ['sometimes', 'string'],
            'configuration.mail.port'           => ['sometimes', 'integer'],
            'configuration.mail.username'       => ['sometimes', 'string'],
            'configuration.mail.password'       => ['sometimes', 'string'],
            'configuration.cache'               => ['sometimes', 'array'],
            'configuration.cache.driver'        => ['sometimes', 'string', 'in:redis,memcached,database,file'],
            'configuration.queue'               => ['sometimes', 'array'],
            'configuration.queue.connection'    => ['sometimes', 'string'],
            'configuration.message_broker'      => ['sometimes', 'array'],
            'configuration.message_broker.driver' => ['sometimes', 'string', 'in:rabbitmq,kafka'],
            'configuration.services'            => ['sometimes', 'array'],
        ];
    }
}
