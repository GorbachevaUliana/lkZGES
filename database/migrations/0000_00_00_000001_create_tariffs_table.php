<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Понижающий коэффициент (просто для информации в админке)
            $table->decimal('coefficient', 5, 4)->nullable();

            // Цены для трех диапазонов
            $table->decimal('price_1', 10, 2); // до 3900 кВт*ч
            $table->decimal('price_2', 10, 2); // 3901 - 6000 кВт*ч
            $table->decimal('price_3', 10, 2); // свыше 6000 кВт*ч

            // Период действия
            $table->date('starts_at'); // Дата начала (например, 2026-01-01)
            $table->date('ends_at')->nullable();   // Дата окончания (если тариф временный)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
