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
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->string('website')->nullable()->after('logo');
            $table->enum('status', ['Negeri', 'Swasta'])->default('Negeri')->after('website');
            $table->text('visi')->nullable()->after('status');
            $table->text('misi')->nullable()->after('visi');
            $table->text('keterangan')->nullable()->after('misi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->dropColumn(['website', 'status', 'visi', 'misi', 'keterangan']);
        });
    }
};