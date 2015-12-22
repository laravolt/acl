<?php

namespace Laravolt\Acl\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\DB;
use Laravolt\Acl\Models\Permission;

class SyncPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acl:sync-permission {--clear}';

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

        if ($this->option('clear')) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            DB::table(with(new Permission)->getTable())->truncate();
        }

        $enumClass = $this->config->get('acl.permission_enum');
        $permissions = $enumClass::toArray();

        $items = collect();
        foreach ($permissions as $name) {
            $permission = Permission::firstOrNew(['name' => $name]);
            $status = 'No Change';

            if (!$permission->exists) {
                $permission->save();
                $status = 'New';
            }

            $items->push(['id' => $permission->getKey(), 'name' => $name, 'status' => $status]);
        }

        $items = $items->sortBy('id');

        $this->table(['ID', 'Name', 'Status'], $items);

    }
}
