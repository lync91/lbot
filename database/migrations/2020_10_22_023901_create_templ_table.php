<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('templ');
        Schema::create('templ', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('step');
            $table->tinyInteger('loaihang')->nullable(true);
            $table->string('tenhang')->nullable(true);
            $table->string('chatlieu')->nullable(true);
            $table->string('quycach')->nullable(true);
            $table->string('gia')->nullable(true);
            $table->tinyInteger('giakm')->nullable(true);
            $table->tinyInteger('soluong')->nullable(true);
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
        Schema::dropIfExists('templ');
    }
}
