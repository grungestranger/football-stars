<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_stats', function (Blueprint $table) {
            $table->integer('player_id')->unsigned();
            $table->integer('match_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->tinyInteger('in_time')->unsigned()->nullable();
            $table->tinyInteger('out_time')->unsigned()->nullable();
            $table->tinyInteger('goals_count')->unsigned();
            $table->json('goals_time')->nullable();
            $table->tinyInteger('yellow_count')->unsigned();
            $table->json('yellow_time')->nullable();
            $table->tinyInteger('red_time')->unsigned()->nullable();
            $table->primary(['player_id', 'match_id']);
            $table->index(['match_id', 'user_id']);
            $table->index(['user_id', 'player_id']);
            $table->index(['player_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_stats');
    }
}
