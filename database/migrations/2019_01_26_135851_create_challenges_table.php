<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->integer('from_user_id')->unsigned();
            $table->integer('to_user_id')->unsigned();
            $table->timestamp('created_at');
            $table->primary(['from_user_id', 'to_user_id']);
            $table->index(['from_user_id', 'created_at']);
            $table->index(['to_user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenges');
    }
}
