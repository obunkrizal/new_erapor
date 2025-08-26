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
        Schema::table('pembayaran_spps', function (Blueprint $table) {
            $table->foreignId('original_payment_id')
                  ->nullable()
                  ->after('catatan')
                  ->constrained('pembayaran_spps')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_spps', function (Blueprint $table) {
            $table->dropForeign(['original_payment_id']);
            $table->dropColumn('original_payment_id');
        });
    }
};
