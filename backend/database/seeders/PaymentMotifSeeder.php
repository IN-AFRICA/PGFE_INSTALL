<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FeeType;
use App\Models\PaymentMotif;
use Illuminate\Database\Seeder;

final class PaymentMotifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $motifs = [];

        // Monthly motifs (Jan to Dec)
        $months = [
            'Janvier',
            'Février',
            'Mars',
            'Avril',
            'Mai',
            'Juin',
            // 'Juillet',
            // 'Août',
            'Septembre',
            'Octobre',
            'Novembre',
            'Décembre',
        ];
        $monthly = FeeType::where('code', 'monthly')->first();
        if ($monthly) {
            foreach ($months as $index => $month) {
                $motifs[] = [
                    'fee_type_id' => $monthly->id,
                    'name' => $month,
                    'code' => mb_strtolower($month),
                    'description' => 'Paiement pour le mois de '.$month,
                ];
            }
        }

        // Quarterly motifs
        $quarters = ['Trimestre 1', 'Trimestre 2', 'Trimestre 3'];
        $quarterly = FeeType::where('code', 'quarterly')->first();
        if ($quarterly) {
            foreach ($quarters as $i => $label) {
                $motifs[] = [
                    'fee_type_id' => $quarterly->id,
                    'name' => $label,
                    'code' => 'trimestre_'.($i + 1),
                    'description' => 'Paiement pour '.$label,
                ];
            }
        }

        // Annual motifs (ex: Année 2023, 2024, 2025)
        $annual = FeeType::where('code', 'annual')->first();
        if ($annual) {
            foreach (range(date('Y'), date('Y') + 2) as $year) {
                $motifs[] = [
                    'fee_type_id' => $annual->id,
                    'name' => 'Année '.$year,
                    'code' => 'year_'.$year,
                    'description' => 'Paiement pour l\'année scolaire '.$year,
                ];
            }
        }

        // Enregistrement des motifs
        foreach ($motifs as $motif) {
            PaymentMotif::updateOrCreate(['code' => $motif['code']], $motif);
        }
    }
}
