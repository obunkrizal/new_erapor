<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (!Schema::hasColumn('kelas', 'kapasitas')) {
                $table->integer('kapasitas')->nullable()->after('periode_id');
            }
        });
    }

    public function down()
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (Schema::hasColumn('kelas', 'kapasitas')) {
                $table->dropColumn('kapasitas');
            }
        });
    }
};
