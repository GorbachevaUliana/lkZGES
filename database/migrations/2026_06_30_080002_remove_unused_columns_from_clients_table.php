<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Проверено вручную: SQLite не даёт удалить колонку, которая
        // участвует во внешнем ключе, никаким ALTER TABLE — ни напрямую,
        // ни через dropForeign() отдельным шагом, ни с
        // PRAGMA foreign_keys=OFF. Единственный настоящий способ на
        // SQLite — пересоздать таблицу целиком, что внутри миграции
        // неоправданно хрупко (пришлось бы вручную дублировать всю
        // остальную схему clients).
        //
        // Реальной базе (MySQL/PostgreSQL) это ограничение не касается —
        // там колонки просто удаляются, как и раньше. На SQLite (только
        // тесты, in-memory) этот шаг безопасно пропускаем: колонки и так
        // нигде в коде не используются, их физическое присутствие в
        // тестовой схеме ни на что не влияет.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['tariff_id']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['tariff_id', 'tariff_category']);
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('tariff_id')->nullable();
            $table->string('tariff_category')->nullable();
        });
    }
};