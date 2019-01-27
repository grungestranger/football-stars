<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('name');
            $table->tinyInteger('speed')->unsigned();
            $table->tinyInteger('acceleration')->unsigned();
            $table->tinyInteger('coordination')->unsigned();
            $table->tinyInteger('power')->unsigned();
            $table->tinyInteger('accuracy')->unsigned();
            $table->tinyInteger('vision')->unsigned();
            $table->tinyInteger('reaction')->unsigned();
            $table->tinyInteger('in_gate')->unsigned();
            $table->tinyInteger('on_out')->unsigned();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('players');
    }
}
