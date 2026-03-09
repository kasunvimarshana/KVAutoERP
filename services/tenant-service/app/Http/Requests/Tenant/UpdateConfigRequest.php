<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'config'               => ['required', 'array'],

            // Optional typed sub-keys
            'database_config'      => ['sometimes', 'array'],
            'database_config.host' => ['sometimes', 'string'],
            'database_config.port' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'database_config.database' => ['sometimes', 'string'],
            'database_config.username' => ['sometimes', 'string'],
            'database_config.password' => ['sometimes', 'string'],
            'database_config.driver'   => ['sometimes', 'string', 'in:mysql,pgsql,sqlite,sqlsrv'],

            'mail_config'              => ['sometimes', 'array'],
            'mail_config.host'         => ['sometimes', 'string'],
            'mail_config.port'         => ['sometimes', 'integer'],
            'mail_config.encryption'   => ['sometimes', 'string', 'in:tls,ssl,starttls'],
            'mail_config.username'     => ['sometimes', 'string'],
            'mail_config.password'     => ['sometimes', 'string'],
            'mail_config.from_address' => ['sometimes', 'email'],
            'mail_config.from_name'    => ['sometimes', 'string'],

            'cache_config'         => ['sometimes', 'array'],
            'cache_config.driver'  => ['sometimes', 'string', 'in:redis,memcached,array,file'],

            'broker_config'        => ['sometimes', 'array'],
            'broker_config.driver' => ['sometimes', 'string'],
            'broker_config.host'   => ['sometimes', 'string'],
            'broker_config.port'   => ['sometimes', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'config.required' => 'A config array is required.',
            'config.array'    => 'Config must be an object/array.',
        ];
    }
}
