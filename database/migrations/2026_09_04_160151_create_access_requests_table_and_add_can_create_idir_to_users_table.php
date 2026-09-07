<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add can_create_idir column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_create_idir')->default(false)->after('phone_verified_at');
        });

        // 2. Create access_requests table
        Schema::create('access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('idir_name');
            $table->string('membership_basis')->nullable();
            $table->string('region')->nullable();
            $table->string('sub_city')->nullable();
            $table->text('purpose')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'granted', 'denied'
            $table->text('denial_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 3. Backfill existing active idir chairs/members and platform owners so they are unaffected
        DB::table('users')->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('idir_user')
                ->whereColumn('idir_user.user_id', 'users.id');
        })->orWhere('is_platform_owner', true)->update(['can_create_idir' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_requests');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_create_idir');
        });
    }
};
