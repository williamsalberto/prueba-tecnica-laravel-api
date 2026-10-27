<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'Dólar Estadounidense',
                'symbol' => 'USD',
                'exchange_rate' => 1.0000,
            ],
            [
                'name' => 'Euro',
                'symbol' => 'EUR',
                'exchange_rate' => 0.9200,
            ],
            [
                'name' => 'Libra Esterlina',
                'symbol' => 'GBP',
                'exchange_rate' => 0.7800,
            ],
            [
                'name' => 'Peso Colombiano',
                'symbol' => 'COP',
                'exchange_rate' => 4150.0000,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['symbol' => $currency['symbol']],
                $currency
            );
        }
    }
}
