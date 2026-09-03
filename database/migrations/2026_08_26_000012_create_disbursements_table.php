<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete(); // recipient
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('cash'); // cash, bank_transfer
            $table->foreignId('recorded_by_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
