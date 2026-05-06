<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Tariff::create([
            'name' => 'Сельское население',
            'price_1' => 4.97,
            'price_2' => 6.65,
            'price_3' => 7.85,
            'starts_at' => '2024-01-01',
        ]);

        \App\Models\Tariff::create([
            'name' => 'Городское население (с электроплитами)',
            'price_1' => 5.45,
            'price_2' => 7.29,
            'price_3' => 8.61,
            'starts_at' => '2024-01-01',
        ]);
    }
}
