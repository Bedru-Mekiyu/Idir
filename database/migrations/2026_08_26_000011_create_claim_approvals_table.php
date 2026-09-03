<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete();
            $table->foreignId('approver_member_id')->constrained('members')->cascadeOnDelete();
            $table->string('decision'); // approved, rejected
            $table->decimal('approved_amount', 12, 2)->nullable(); // Approver's agreed/overridden amount
            $table->text('remarks')->nullable(); // Required if decision is rejected or amount is overridden
            $table->timestamp('decided_at');
            $table->timestamps();

            // Fix #2: Database-level unique constraint prevents duplicate approvals by the same approver
            $table->unique(['claim_id', 'approver_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_approvals');
    }
};
