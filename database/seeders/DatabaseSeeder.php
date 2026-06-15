<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Администратор
        User::create([
            'name' => 'Горбачева Ульяна Александровна',
            'email' => 'u.gorbacheva@zarseti.ru',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->call([
            TariffSeeder::class, //сидер тарифов
            PdfTemplateSeeder::class, //сидер шаблонов ПДФ
            ApplicationTemplateSeeder::class 
        ]);
    }
}