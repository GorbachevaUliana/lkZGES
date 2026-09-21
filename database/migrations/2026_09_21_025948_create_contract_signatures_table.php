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
        Schema::create('contract_signatures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')->constrained()->onDelete('cascade');

            $table->string('signer');   // organization | client
            $table->string('method');   // pep | ukep

            $table->timestamp('signed_at');

            // Хеш документа НА МОМЕНТ подписания. Главное поле таблицы:
            // именно оно доказывает, какой файл был подписан.
            $table->string('document_hash', 64);

            // Кто совершил действие в системе. У организации сертификат один
            // на всех операторов, поэтому по подписи не понять, кто именно её
            // поставил — ответственность внутри организации ведётся здесь.
            $table->foreignId('signed_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // --- УКЭП ---
            $table->string('signature_file_path')->nullable();
            $table->string('certificate_subject')->nullable();
            $table->string('certificate_serial')->nullable();
            $table->string('certificate_inn', 12)->nullable();
            $table->timestamp('certificate_valid_from')->nullable();
            $table->timestamp('certificate_valid_to')->nullable();

            // --- ПЭП ---
            $table->string('pep_channel')->nullable();  // email | sms
            $table->string('pep_sent_to')->nullable();  // маскированный адрес

            // --- Обстоятельства подписания ---
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['contract_id', 'signer']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_signatures');
    }
};
