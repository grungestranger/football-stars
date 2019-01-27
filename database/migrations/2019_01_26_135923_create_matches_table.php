<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user1_id')->unsigned();
            $table->integer('user2_id')->unsigned();
            $table->enum('result', ['draw', 'win1', 'win2'])->nullable();
            $table->timestamp('created_at');
            $table->index(['user1_id', 'created_at']);
            $table->index(['user2_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches');
    }
}
