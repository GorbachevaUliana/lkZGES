<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            // Один объект — одно показание в месяц
            $table->unique(['property_id', 'reading_date'], 'unique_reading_per_property_month');
        });
    }

    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropUnique('unique_reading_per_property_month');
        });
    }
};
