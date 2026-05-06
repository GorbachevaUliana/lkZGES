<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            
            // Лицевой счет (заполняется админом при одобрении)
            $table->string('account_number')->nullable()->unique();
            
            // Адрес конкретного объекта
            $table->text('address');
            
            // Статус: pending (ожидает ЛС), active (можно слать показания), archived
            $table->string('status')->default('pending');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};