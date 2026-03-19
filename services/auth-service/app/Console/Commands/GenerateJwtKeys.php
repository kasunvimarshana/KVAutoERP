<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateJwtKeys extends Command
{
    protected $signature   = 'jwt:generate-keys {--force : Overwrite existing keys}';
    protected $description = 'Generate a 4096-bit RSA key pair for JWT RS256 signing';

    public function handle(): int
    {
        $keysDir        = storage_path('keys');
        $privateKeyPath = storage_path('keys/private.pem');
        $publicKeyPath  = storage_path('keys/public.pem');

        if (! is_dir($keysDir)) {
            mkdir($keysDir, 0700, true);
        }

        if (file_exists($privateKeyPath) && ! $this->option('force')) {
            $this->warn('JWT keys already exist. Use --force to overwrite.');
            return self::SUCCESS;
        }

        $resource = openssl_pkey_new([
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            $this->error('Failed to generate RSA key: ' . openssl_error_string());
            return self::FAILURE;
        }

        openssl_pkey_export($resource, $privateKeyPem);
        $details      = openssl_pkey_get_details($resource);
        $publicKeyPem = $details['key'];

        file_put_contents($privateKeyPath, $privateKeyPem);
        chmod($privateKeyPath, 0600);

        file_put_contents($publicKeyPath, $publicKeyPem);
        chmod($publicKeyPath, 0644);

        $this->info('✔ JWT key pair generated.');
        $this->line("  Private key: {$privateKeyPath}");
        $this->line("  Public key:  {$publicKeyPath}");

        return self::SUCCESS;
    }
}
