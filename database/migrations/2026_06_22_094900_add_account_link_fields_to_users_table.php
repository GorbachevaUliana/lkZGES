<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Поля для двухшагового процесса привязки лицевого счёта.
     *
     * link_code         — хэш 6-значного кода (bcrypt), null когда кода нет.
     * link_code_expires — когда код истекает (15 минут с момента отправки).
     * link_client_id    — какого именно клиента пытаются привязать на шаге 1,
     *                     чтобы не искать заново на шаге 2.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('link_code')->nullable()->after('remember_token');
            $table->timestamp('link_code_expires')->nullable()->after('link_code');
            $table->foreignId('link_client_id')
                ->nullable()
                ->after('link_code_expires')
                ->constrained('clients')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['link_client_id']);
            $table->dropColumn(['link_code', 'link_code_expires', 'link_client_id']);
        });
    }
};