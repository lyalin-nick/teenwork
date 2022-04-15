<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_offers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('task_id', false, true);
            $table->bigInteger('user_id', false, true);
            $table->text('text')->nullable();
            $table->boolean('accept')->nullable();
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
        Schema::dropIfExists('task_offers');
    }
}
