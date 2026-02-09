<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AccountNumber;
use Illuminate\Database\Seeder;

final class SubAccountSeeder extends Seeder
{
    public function run(): void
    {
        //        $parent = AccountNumber::where('number', '1000')->first();
        //        AccountNumber::firstOrCreate([
        //            'number' => '1010',
        //            'account_type_id' => $parent?->account_type_id,
        //            'label' => 'Caisse',
        //        ]);
        //        AccountNumber::firstOrCreate([
        //            'number' => '1020',
        //            'account_type_id' => $parent?->account_type_id,
        //            'label' => 'Banque',
        //        ]);
    }
}
