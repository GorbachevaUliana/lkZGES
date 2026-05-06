<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица для хранения шаблонов PDF-документов
 *
 * Администраторы сайта могут редактировать шаблоны через Filament
 * без изменения кода
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('client_type')->default('individual');
            $table->string('document_type')->default('application');
            $table->text('content');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::table('application_templates', function (Blueprint $table) {
            $table->foreignId('pdf_template_id')->nullable()->after('is_active')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('application_templates', function (Blueprint $table) {
            $table->dropForeign(['pdf_template_id']);
            $table->dropColumn('pdf_template_id');
        });

        Schema::dropIfExists('pdf_templates');
    }
};
