<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('plan_key', 30)->after('user_id');
            $table->string('plan_name', 100)->after('plan_key');
            $table->unsignedInteger('daily_interest_bps')->after('plan_name');
            $table->unsignedSmallInteger('duration_days')->after('daily_interest_bps');
            $table->unsignedBigInteger('min_amount_cents')->after('duration_days');
            $table->unsignedBigInteger('max_amount_cents')->after('min_amount_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'plan_key',
                'plan_name',
                'daily_interest_bps',
                'duration_days',
                'min_amount_cents',
                'max_amount_cents',
            ]);
        });
    }
};
