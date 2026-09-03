<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->string('event_type'); // due_reminder, late_warning, etc.
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('telegram_enabled')->default(false);
            $table->boolean('in_app_enabled')->default(true);
            $table->text('template_am')->nullable();
            $table->text('template_en')->nullable();
            $table->timestamps();

            // Fix #5: Unique constraint per idir per event_type
            $table->unique(['idir_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
