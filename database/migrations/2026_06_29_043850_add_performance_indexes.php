<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->index('status', 'idx_properties_status');
        });

        Schema::table('meter_readings', function (Blueprint $table) {
            $table->index('reading_date', 'idx_meter_readings_date');
            $table->index('is_paid', 'idx_meter_readings_is_paid');
            $table->index(['property_id', 'reading_date'], 'idx_meter_readings_property_date');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('idx_properties_status');
        });

        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropIndex('idx_meter_readings_date');
            $table->dropIndex('idx_meter_readings_is_paid');
            $table->dropIndex('idx_meter_readings_property_date');
        });
    }
};
