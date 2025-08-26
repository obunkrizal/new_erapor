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

        $table->enum('status_pembayaran', ['lunas', 'belum lunas'])->default('belum lunas');
        $table->decimal('sisa_hutang', 12, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_spps', function (Blueprint $table) {
            $table->dropColumn('status_pembayaran');
            $table->dropColumn('sisa_hutang');
        });
    }
};
