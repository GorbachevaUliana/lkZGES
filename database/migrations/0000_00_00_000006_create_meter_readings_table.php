<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('tariff_id')->nullable()->constrained('tariffs')->onDelete('set null');
            $table->integer('previous_value')->default(0);
            $table->integer('current_value');
            $table->integer('consumed')->virtualAs('current_value - previous_value');
            $table->decimal('total_sum', 10, 2)->nullable();
            $table->date('reading_date');
            $table->boolean('is_paid')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
