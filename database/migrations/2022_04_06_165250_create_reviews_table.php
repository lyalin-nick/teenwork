<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('task_id', false, true);
            $table->bigInteger('user_id', false, true);
            $table->bigInteger('reviewer_id', false, true);
            $table->smallInteger('rating', false, true)->default(0);
            $table->text('text');
            $table->date('date')->nullable();
            $table->timestamps();
            $table->unique(['task_id', 'user_id', 'reviewer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
