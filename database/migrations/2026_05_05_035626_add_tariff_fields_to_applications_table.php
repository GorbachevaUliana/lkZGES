<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('tariff_id')->nullable()->constrained('tariffs')->onDelete('set null');
            $table->string('tariff_category')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['tariff_id']);
            $table->dropColumn(['tariff_id', 'tariff_category']);
        });
    }
};
