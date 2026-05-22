<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_slot_capacities', function (Blueprint $table) {
            $table->id();
            $table->string('plan_key')->unique();
            $table->unsignedInteger('slot_capacity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_slot_capacities');
    }
};
