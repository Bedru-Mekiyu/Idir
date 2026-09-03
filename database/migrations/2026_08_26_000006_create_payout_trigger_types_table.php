<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_trigger_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->string('name'); // e.g., 'death', 'wedding', 'emergency'
            $table->string('label_am'); // Amharic label e.g., 'ሞት', 'ሰርግ'
            $table->decimal('default_payout_amount', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_trigger_types');
    }
};
