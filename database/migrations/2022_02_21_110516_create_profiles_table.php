<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id', false, true);
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('about')->nullable();
            $table->string('status')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->string('photo_name', 255)->nullable();
            $table->string('photo_ext', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->text('place_id')->nullable();
            $table->integer('number_performer_tasks', false, true)->default(0);
            $table->integer('number_employer_tasks', false, true)->default(0);
            $table->float('rating', 3, 2, true)->default(0.00);
            $table->integer('number_review', false, true)->default(0);
            $table->boolean('push_notification')->default(false);
            $table->boolean('email_notification')->default(false);
            $table->boolean('invisible')->default(false);
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
        Schema::dropIfExists('profiles');
    }
}
