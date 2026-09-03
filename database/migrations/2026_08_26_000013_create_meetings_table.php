<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->string('type')->default('general_assembly'); // general_assembly, executive, trustee
            $table->date('meeting_date');
            $table->text('minutes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('recorded_by_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
