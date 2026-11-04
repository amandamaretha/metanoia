<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->bigInteger('nilai_sebelum_pembulatan')->default(0)->after('harga_per_kg');
            $table->bigInteger('pembulatan')->default(0)->after('nilai_sebelum_pembulatan');
        });

        // Sinkronisasi data lama jika ada
        DB::table('setoran')
            ->where('nilai_sebelum_pembulatan', 0)
            ->update(['nilai_sebelum_pembulatan' => DB::raw('total_nilai')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->dropColumn(['nilai_sebelum_pembulatan', 'pembulatan']);
        });
    }
};

