<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoInvToPembayaranSppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pembayaran_spps', function (Blueprint $table) {
            // $table->string('no_inv')->nullable()->after('id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pembayaran_spps', function (Blueprint $table) {
            // $table->dropColumn('no_inv');
        });
    }
}
