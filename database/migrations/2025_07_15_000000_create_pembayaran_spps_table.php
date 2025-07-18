<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranSppsTable extends Migration
{
    public function up()
    {
        Schema::create('pembayaran_spps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method',['cash','transfer']);
            $table->enum('month', ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september','october','november','december'])->default('january');
            $table->date('payment_date');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembayaran_spps');
    }
}
