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
        // Schema::create('clients', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('last_name')->nullable()->change();
        //     $table->string('first_name')->nullable()->change();
        //     $table->string('middle_name')->nullable()->change();
        //     $table->string('company_name')->nullable()->change();
        //     $table->string('account_number')->unique()->nullable()->change();
        //     $table->string('email')->unique();
        //     $table->string('status')->default('applicant')->after('email');
        //     $table->string('address');
        //     $table->string('phone', 20);
        //     $table->foreignId('tariff_id')->nullable()->constrained('tariffs')->onDelete('set null');
        //     $table->string('tariff_category')->nullable()->after('id')->index();
        //     $table->date('contract_date')->nullable();
        //     $table->timestamps();
        // });
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            // Связь с пользователем
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            // Тип и основные данные
            $table->string('client_type')->default('individual');
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            
            // Для юрлиц
            $table->string('company_name')->nullable();
            $table->string('inn')->nullable();
            $table->string('kpp')->nullable();
            $table->string('ogrn')->nullable();
            $table->string('contact_person')->nullable();
            
            $table->string('phone', 20);
            $table->string('email')->nullable();
            
            // Системные поля
            $table->string('tariff_category')->nullable()->index();
            $table->date('contract_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
