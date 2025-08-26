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
            $table->dateTime('tanggal_pelunasan')
                  ->nullable()
                  ->after('payment_date')
                  ->comment('Tanggal ketika pembayaran dinyatakan lunas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_spps', function (Blueprint $table) {
            $table->dropColumn('tanggal_pelunasan');
        });
    }
};
