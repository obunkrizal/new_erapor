<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nilais', function (Blueprint $table) {
            // Change image columns to JSON to store multiple files
            $table->json('fotoAgama')->nullable()->change();
            $table->json('fotoJatiDiri')->nullable()->change();
            $table->json('fotoLiterasi')->nullable()->change();
            $table->json('fotoNarasi')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('nilais', function (Blueprint $table) {
        });
    }
};

            
