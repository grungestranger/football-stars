<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTableUserSchemasToSchemas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('user_schemas', 'schemas');

        Schema::table('schemas', function (Blueprint $table) {
            $table->renameIndex('user_schemas_user_id_name_unique', 'schemas_user_id_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('schemas', 'user_schemas');

        Schema::table('user_schemas', function (Blueprint $table) {
            $table->renameIndex('schemas_user_id_name_unique', 'user_schemas_user_id_name_unique');
        });
    }
}
