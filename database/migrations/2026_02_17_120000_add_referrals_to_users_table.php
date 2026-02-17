<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code')->nullable()->unique()->after('password');
            $table->foreignId('referrer_id')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');
        });

        $duplicateNames = DB::table('users')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('count(*) > 1')
            ->pluck('name');

        foreach ($duplicateNames as $name) {
            $users = DB::table('users')
                ->where('name', $name)
                ->orderBy('id')
                ->get(['id', 'name']);

            $skipFirst = true;
            foreach ($users as $user) {
                if ($skipFirst) {
                    $skipFirst = false;
                    continue;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['name' => $user->name.'-'.$user->id]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('name');
        });

        $userIds = DB::table('users')->whereNull('referral_code')->pluck('id');

        foreach ($userIds as $userId) {
            do {
                $code = Str::upper(Str::random(10));
            } while (DB::table('users')->where('referral_code', $code)->exists());

            DB::table('users')->where('id', $userId)->update([
                'referral_code' => $code,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropForeign(['referrer_id']);
            $table->dropColumn(['referrer_id', 'referral_code']);
        });
    }
};
