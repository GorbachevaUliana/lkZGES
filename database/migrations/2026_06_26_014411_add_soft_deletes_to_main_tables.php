<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft deletes для ключевых моделей.
 *
 * Вместо физического удаления записей проставляется deleted_at.
 * Это критично для энергосбытовой организации:
 * — данные клиентов нельзя уничтожать (152-ФЗ, возможные споры)
 * — показания счётчиков — финансовые документы
 * — заявки и документы нужны для аудита
 */
return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'clients',
            'documents',
            'applications',
            'properties',
            'meter_readings',
            'tickets',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'clients',
            'documents',
            'applications',
            'properties',
            'meter_readings',
            'tickets',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};