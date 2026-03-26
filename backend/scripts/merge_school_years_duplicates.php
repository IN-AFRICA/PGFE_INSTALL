<?php
// Script temporaire à lancer avec: php scripts/merge_school_years_duplicates.php
use Illuminate\Database\Capsule\Manager as DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class);

// 1. Pour chaque nom d'année, garder la plus ancienne (id min), réaffecter les clés étrangères, puis supprimer les doublons
$names = DB::table('school_years')->select('name')->groupBy('name')->pluck('name');
foreach ($names as $name) {
    $ids = DB::table('school_years')->where('name', $name)->orderBy('id')->pluck('id');
    $mainId = $ids->first();
    // Réaffecter les entités liées
    DB::table('fiche_cotations')->whereIn('school_year_id', $ids)->where('school_year_id', '!=', $mainId)->update(['school_year_id' => $mainId]);
    DB::table('registrations')->whereIn('school_year_id', $ids)->where('school_year_id', '!=', $mainId)->update(['school_year_id' => $mainId]);
    // Supprimer les doublons (hors main)
    DB::table('school_years')->where('name', $name)->where('id', '!=', $mainId)->delete();
}
echo "Fusion terminée. Les doublons ont été supprimés.\n";
