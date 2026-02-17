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
            $table->unsignedBigInteger('interest_earned_cents')->default(0)->after('amount_cents');
            $table->unsignedSmallInteger('accrued_days')->default(0)->after('interest_earned_cents');
            $table->timestamp('last_interest_at')->nullable()->after('accrued_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['interest_earned_cents', 'accrued_days', 'last_interest_at']);
        });
    }
};
