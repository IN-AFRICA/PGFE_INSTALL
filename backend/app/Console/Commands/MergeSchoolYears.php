<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeSchoolYears extends Command
{
    protected $signature = 'merge:school-years';
    protected $description = 'Fusionne les doublons d\'années scolaires et réaffecte les clés étrangères.';

    public function handle()
    {
        $names = DB::table('school_years')->select('name')->groupBy('name')->pluck('name');
        $affected = 0;
        foreach ($names as $name) {
            $ids = DB::table('school_years')->where('name', $name)->orderBy('id')->pluck('id');
            $mainId = $ids->first();
            // Réaffecter les entités liées
            $affected += DB::table('fiche_cotations')->whereIn('school_year_id', $ids)->where('school_year_id', '!=', $mainId)->update(['school_year_id' => $mainId]);
            $affected += DB::table('registrations')->whereIn('school_year_id', $ids)->where('school_year_id', '!=', $mainId)->update(['school_year_id' => $mainId]);
            // Supprimer les doublons (hors main)
            DB::table('school_years')->where('name', $name)->where('id', '!=', $mainId)->delete();
        }
        $this->info("Fusion terminée. $affected références réaffectées. Les doublons ont été supprimés.");
    }
}
