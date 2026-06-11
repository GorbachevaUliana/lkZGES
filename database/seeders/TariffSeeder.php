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
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-09-30',
        ]);

        \App\Models\Tariff::create([
            'name' => 'Городское население с электроплитами и (или) электроотопительными установками',
            'price_1' => 5.45,
            'price_2' => 7.29,
            'price_3' => 8.61,
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-09-30',
        ]);

        \App\Models\Tariff::create([
            'name' => 'Городское население без электроплит и (или) электроотопительных установок',
            'price_1' => 6.36,
            'price_2' => 8.51,
            'price_3' => 10.05,
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-09-30',
        ]);

        \App\Models\Tariff::create([
            'name' => 'Садоводческие, огороднические или дачные некоммерческие объединения граждан',
            'price_1' => 4.97,
            'price_2' => 6.65,
            'price_3' => 7.85,
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-09-30',
        ]);

        \App\Models\Tariff::create([
            'name' => 'Гаражные кооперативы, владельцы гаражей, погребов, учреждения исполнения наказания, религиозные организации',
            'price_1' => 6.36,
            'price_2' => 8.51,
            'price_3' => 10.05,
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-09-30',
        ]);
    }
}
