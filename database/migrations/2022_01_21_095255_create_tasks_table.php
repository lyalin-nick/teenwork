<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id', false, true)->nullable();
            $table->bigInteger('category_id', false, true);
            $table->string('name', 255);
            $table->text('description');
            $table->text('result');
            $table->string('address');
            $table->text('place_id');
            $table->double('lat', 10, 6)->nullable();
            $table->double('lng', 10, 6)->nullable();
            $table->date('start_date');
            $table->time('start_time')->default('00:00:00');
            $table->smallInteger('amount_of_workers', false, true)->default(0);
            $table->smallInteger('minimum_age', false, true)->default(0);
            $table->integer('price', false, true)->default(0);
            $table->string('payment_type');
            $table->boolean('safe_deal')->default(false);
            $table->boolean('hot_work')->default(false);
            $table->boolean('account_verified')->default(false);
            $table->tinyInteger('status')->default(1);
            $table->integer('views_number')->default(0);
            $table->dateTime('expired_at')->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
