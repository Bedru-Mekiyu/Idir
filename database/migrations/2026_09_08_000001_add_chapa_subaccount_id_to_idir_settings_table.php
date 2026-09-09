<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idir_settings', function (Blueprint $table) {
            $table->string('chapa_subaccount_id')->nullable()->after('fund_balance');
        });
    }

    public function down(): void
    {
        Schema::table('idir_settings', function (Blueprint $table) {
            $table->dropColumn('chapa_subaccount_id');
        });
    }
};
