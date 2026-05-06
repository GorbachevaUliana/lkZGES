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
        // Schema::create('applications', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('user_id')->constrained()->onDelete('cascade');
        //     $table->foreignId('template_id')->constrained('application_templates');
        //     $table->foreignId('tariff_id')
        //         ->nullable()
        //         ->constrained('tariffs')
        //         ->onDelete('set null');
        //     $table->json('data');
        //     $table->string('status');
        //     $table->text('admin_comment')->nullable();
        //     $table->timestamps();
        // });
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->constrained('application_templates');
            
            // Связь с объектом (черновиком), который создается при подаче
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');

            $table->string('client_type')->default('individual');
            $table->json('data');
            $table->string('status'); // pending, processing, approved, rejected
            
            // Пути к документам
            $table->string('generated_pdf_path')->nullable();
            $table->string('contract_pdf_path')->nullable();

            // Обработка
            $table->text('admin_comment')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
