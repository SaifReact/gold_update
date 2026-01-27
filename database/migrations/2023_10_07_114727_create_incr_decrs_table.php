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
        Schema::create('incr_decrs', function (Blueprint $table) {
            $table->id();
            $table->string('type_id', 5)->nullable();
            $table->string('type', 20)->nullable();
            $table->string('money', 20)->nullable();
            $table->decimal('incr', 10, 2)->nullable();
            $table->decimal('decr', 10, 2)->nullable();
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
        Schema::dropIfExists('incr_decrs');
    }
};
