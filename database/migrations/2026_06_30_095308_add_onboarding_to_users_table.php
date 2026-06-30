<?php

declare(strict_types=1);

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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('onboarding_step')->nullable()->after('mfa_prompted_at');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
        });

        // Backfill existing users: set onboarding_completed_at to their creation date
        // so they are not forced through the onboarding wizard
        DB::table('users')
            ->whereNull('onboarding_completed_at')
            ->update(['onboarding_completed_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_step', 'onboarding_completed_at']);
        });
    }
};
