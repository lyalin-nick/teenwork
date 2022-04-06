<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique()->nullable();
            $table->string('role', 16)->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('status', 16);
            $table->string('reset_token')->nullable();
            $table->timestamp('reset_token_expire')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('verify_token')->nullable();
            $table->timestamp('verify_token_expire')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
