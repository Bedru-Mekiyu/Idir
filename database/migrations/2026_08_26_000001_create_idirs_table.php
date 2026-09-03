<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idirs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('membership_basis')->nullable(); // Free text: neighborhood, workplace, church, etc.
            $table->string('region')->nullable();
            $table->string('sub_city')->nullable();
            $table->string('woreda')->nullable();
            $table->string('locale')->default('am');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idirs');
    }
};
