<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_signatures', function (Blueprint $table) {
            // Хеш самого файла подписи. Имя файла внутреннее и ничего
            // не доказывает; хеш позволяет подтвердить, что предъявленный
            // файл подписи — тот же, что был загружен.
            $table->string('signature_file_hash', 64)->nullable()->after('signature_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('contract_signatures', function (Blueprint $table) {
            $table->dropColumn('signature_file_hash');
        });
    }
};
