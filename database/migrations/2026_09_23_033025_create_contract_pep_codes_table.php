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
        Schema::create('contract_pep_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')->constrained()->onDelete('cascade');

            // Хеш кода, а не сам код: в базе не должно быть того,
            // чем можно подписать договор.
            $table->string('code_hash');

            $table->string('channel');   // email | sms
            $table->string('sent_to');   // куда фактически ушёл код

            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamp('expires_at');
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            $table->index(['contract_id', 'confirmed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_pep_codes');
    }
};
