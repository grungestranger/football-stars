<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_settings', function (Blueprint $table) {
            $table->integer('schema_id')->unsigned();
            $table->integer('player_id')->unsigned();
            $table->json('settings');
            $table->primary(['schema_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_settings');
    }
}
