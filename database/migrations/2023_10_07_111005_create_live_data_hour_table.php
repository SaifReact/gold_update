<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_data_hours', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->nullable();
            $table->decimal('bid_sell', 10, 2)->nullable();
            $table->decimal('ask_buy', 10, 2)->nullable();
            $table->decimal('low', 10, 2)->nullable();
            $table->decimal('high', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('live_data_hours');
    }
};
