<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

final class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['name' => 'Espèces', 'code' => 'cash'],
            ['name' => 'Mobile Money', 'code' => 'mobile_money'],
            ['name' => 'Virement Bancaire', 'code' => 'bank_transfer'],
            ['name' => 'Chèque', 'code' => 'cheque'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(['code' => $method['code']], $method);
        }
    }
}
