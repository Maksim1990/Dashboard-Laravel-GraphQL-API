<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCloudFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cloud_folders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('main')->nullable();
            $table->string('images')->nullable();
            $table->string('posts')->nullable();
            $table->string('documents')->nullable();
            $table->string('messages')->nullable();
            $table->string('storage')->nullable();
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
        Schema::dropIfExists('user_cloud_folders');
    }
}
