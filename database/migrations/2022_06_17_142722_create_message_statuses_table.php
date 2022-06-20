<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_statuses', function (Blueprint $table) {
            $table->bigInteger('user_id', false, true);
            $table->bigInteger('message_id', false, true);
            $table->boolean('reading')->default(false);
            $table->unique(['user_id', 'message_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_statuses');
    }
}
