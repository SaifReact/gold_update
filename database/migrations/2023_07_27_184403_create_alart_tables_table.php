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
        Schema::create('alart_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gold_weight');
            $table->float('app_price', 8, 2);
            $table->float('offer_price', 8, 2);
            $table->string('Name');
            $table->string('Mobile_no');
            $table->date('buy_date');
            $table->boolean('status');
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
        Schema::dropIfExists('alart_tables');
    }
};
