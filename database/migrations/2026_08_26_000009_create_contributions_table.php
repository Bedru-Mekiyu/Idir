<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete(); // paid_for
            $table->foreignId('paid_by_member_id')->nullable()->constrained('members')->nullOnDelete(); // if different from member_id
            $table->foreignId('recorded_by_member_id')->nullable()->constrained('members')->nullOnDelete(); // for cash entries
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('cash'); // cash, chapa
            $table->string('type')->default('cash'); // cash, in_kind
            $table->string('in_kind_description')->nullable();
            $table->string('period_covered'); // e.g. "2026-08" or "2026-W34"
            $table->string('chapa_tx_ref')->nullable()->unique();
            $table->string('chapa_status')->nullable(); // pending, verified, failed, expired
            $table->text('notes')->nullable();
            // Fix #1: Explicit boolean column for corrections
            $table->boolean('is_correction')->default(false);
            $table->foreignId('corrected_contribution_id')->nullable()->constrained('contributions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
