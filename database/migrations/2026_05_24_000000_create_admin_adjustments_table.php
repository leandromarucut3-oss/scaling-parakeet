<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->integer('amount_cents');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('admin_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_adjustments');
    }
};
