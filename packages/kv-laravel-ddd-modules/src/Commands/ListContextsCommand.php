<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Contracts\ContextRegistrar;

/**
 * Artisan command: list all discovered DDD bounded contexts.
 */
class ListContextsCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'ddd:list-contexts';

    /** {@inheritdoc} */
    protected $description = 'List all discovered DDD bounded contexts';

    public function __construct(protected ContextRegistrar $registrar)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $basePath = base_path((string) config('ddd.base_path', 'app'));

        $this->registrar->discover($basePath);

        $contexts = $this->registrar->all();

        if (empty($contexts)) {
            $this->warn('No DDD contexts found. Create one with: <comment>php artisan ddd:make-context ContextName</comment>');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($contexts as $context) {
            $path = $context['path'];
            $rows[] = [
                $context['name'],
                $path,
                is_dir($path.DIRECTORY_SEPARATOR.'Domain') ? '✓' : '✗',
                is_dir($path.DIRECTORY_SEPARATOR.'Application') ? '✓' : '✗',
                is_dir($path.DIRECTORY_SEPARATOR.'Infrastructure') ? '✓' : '✗',
            ];
        }

        $this->table(
            ['Context Name', 'Path', 'Has Domain', 'Has Application', 'Has Infrastructure'],
            $rows,
        );

        $this->line('');
        $this->info('Found '.count($contexts).' context(s).');

        return self::SUCCESS;
    }
}
