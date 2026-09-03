<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('phone');
            $table->string('fayda_id')->nullable();
            $table->boolean('fayda_verified')->default(false);
            $table->date('join_date');
            $table->string('status')->default('active'); // active, in_arrears, excluded
            $table->string('committee_role')->nullable(); // chair, secretary, treasurer, or null
            $table->text('exclusion_reason')->nullable();
            $table->timestamp('excluded_at')->nullable();
            $table->timestamp('exclusion_warning_sent_at')->nullable();
            $table->string('telegram_chat_id')->nullable();
            $table->timestamps();

            $table->unique(['idir_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
