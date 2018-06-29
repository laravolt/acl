<?php

namespace Laravolt\Acl\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

class SyncPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravolt:sync-permission {--clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize permission table and config file';

    protected $config;

    /**
     * Create a new command instance.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Synchronize Permissions Entries');

        $result = app('laravolt.acl')->syncPermission($this->option('clear'));

        $this->table(['ID', 'Name', 'Status'], $result);
    }
}
