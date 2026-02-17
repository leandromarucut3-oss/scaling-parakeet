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
            $table->foreignId('referrer_id')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            $table->unsignedBigInteger('referral_commission_cents')->default(0)->after('amount_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn(['referrer_id', 'referral_commission_cents']);
        });
    }
};
