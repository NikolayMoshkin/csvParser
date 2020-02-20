<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->unsignedInteger('timestamp')->unique();
            $table->dateTime('time');
            $table->string('domain_name');
            $table->unsignedInteger('file_size');
            $table->string('file_path');
            $table->string('user_agent');
            $table->integer('http_status');
            $table->string('http_method');
            $table->string('content_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connections');
    }
}
