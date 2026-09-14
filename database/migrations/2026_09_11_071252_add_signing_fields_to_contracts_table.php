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
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('signing_required')->default(false)->after('client_type');
            $table->string('signing_reason')->nullable()->after('signing_required');
            $table->string('signature_method')->nullable()->after('signing_reason');
            $table->decimal('max_power_kw', 10, 2)->nullable()->after('signature_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'signing_required',
                'signing_reason',
                'signature_method',
                'max_power_kw',
            ]);
        });
    }
};
