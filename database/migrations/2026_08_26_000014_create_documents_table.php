<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idir_id')->constrained('idirs')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('category')->default('other'); // bylaws, receipt, correspondence, other
            $table->foreignId('uploaded_by_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->boolean('deletion_requested')->default(false);
            $table->boolean('deletion_confirmed')->default(false);
            $table->foreignId('deletion_confirmed_by')->nullable()->constrained('members')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
