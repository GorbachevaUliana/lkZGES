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
        Schema::table('applications', function (Blueprint $table) {
            $table->decimal('max_power_kw', 10, 2)->nullable()->after('client_type');
            //null - вопрос не задавался (старые заявки)
            // false - потребитель осознанно отказался
            $table->boolean('signing_requested')->nullable()->after('max_power_kw');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['max_power_kw', 'signing_requested']);
        });
    }
};
