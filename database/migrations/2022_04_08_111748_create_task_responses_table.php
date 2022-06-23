<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_responses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('task_id', false, true);
            $table->bigInteger('user_id', false, true);
            $table->bigInteger('chat_id', false, true)->nullable();
            $table->bigInteger('message_id', false, true)->nullable();
            $table->text('text');
            $table->boolean('is_new')->default(true);
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
        Schema::dropIfExists('task_responses');
    }
}
