<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('payout_trigger_type_id')->constrained('payout_trigger_types')->cascadeOnDelete();
            $table->text('description');
            $table->decimal('requested_amount', 12, 2)->nullable(); // null allowed; falls back to default_payout_amount
            $table->string('status')->default('pending'); // pending, under_review, approved, rejected, paid
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
