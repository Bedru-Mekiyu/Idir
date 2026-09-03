<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idir_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->decimal('dues_amount', 12, 2)->default(200.00);
            $table->string('dues_frequency')->default('monthly');
            $table->decimal('late_fee_amount', 12, 2)->nullable();
            $table->integer('late_fee_grace_days')->nullable();
            $table->integer('vesting_period_days')->default(90);
            $table->integer('required_approvals')->default(2);
            $table->json('enabled_payout_triggers')->nullable();
            $table->decimal('fund_balance', 14, 2)->default(0.00); // Derived/cached balance
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idir_settings');
    }
};
