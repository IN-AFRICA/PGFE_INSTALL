<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sync\SyncService;

class SyncWithRemote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-remote {--table= : Specifier une table precise}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise la base locale avec le serveur distant (Multitenant)';

    /**
     * Execute the console command.
     */
    public function handle(SyncService $syncService)
    {
        $this->info("Debut de la synchronisation...");
        
        $table = $this->option('table');
        
        if ($table) {
            $this->info("Synchro de la table : $table");
            $syncService->syncTable($table);
        } else {
            $this->info("Synchro de toutes les tables...");
            $syncService->syncAll();
        }

        $this->info("Synchronisation terminee.");
    }
}
