<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Generates the RSA-2048 public/private key pair used for JWT signing.
 * Run once per environment: php artisan auth:generate-keys
 */
class GenerateJwtKeysCommand extends Command
{
    protected $signature = 'auth:generate-keys
                            {--force : Overwrite existing keys}
                            {--bits=2048 : RSA key size in bits}';

    protected $description = 'Generate RSA public/private key pair for JWT signing';

    public function handle(): int
    {
        $privateKeyPath = base_path(config('jwt.keys.private', 'storage/keys/private.pem'));
        $publicKeyPath  = base_path(config('jwt.keys.public', 'storage/keys/public.pem'));

        if (file_exists($privateKeyPath) && ! $this->option('force')) {
            $this->error('Keys already exist. Use --force to regenerate them.');
            return Command::FAILURE;
        }

        $keyDir = dirname($privateKeyPath);
        if (! is_dir($keyDir)) {
            mkdir($keyDir, 0700, true);
        }

        $bits = (int) $this->option('bits');
        $this->info("Generating {$bits}-bit RSA key pair...");

        $config = [
            'digest_alg'       => 'sha256',
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $privateKey = openssl_pkey_new($config);

        if ($privateKey === false) {
            $this->error('Failed to generate RSA key pair. Ensure OpenSSL extension is enabled.');
            return Command::FAILURE;
        }

        // Export private key
        openssl_pkey_export($privateKey, $privateKeyPem);
        file_put_contents($privateKeyPath, $privateKeyPem);
        chmod($privateKeyPath, 0600);

        // Export public key
        $details = openssl_pkey_get_details($privateKey);
        if ($details === false) {
            $this->error('Failed to extract public key.');
            return Command::FAILURE;
        }

        file_put_contents($publicKeyPath, $details['key']);
        chmod($publicKeyPath, 0644);

        $this->info('✓ Private key written to: ' . $privateKeyPath);
        $this->info('✓ Public key written to:  ' . $publicKeyPath);
        $this->newLine();
        $this->comment('Share the PUBLIC key with other microservices for local JWT verification.');
        $this->comment('Keep the PRIVATE key secret — never share it outside the Auth service.');

        return Command::SUCCESS;
    }
}
