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
            $table->boolean('push_notification')->default(true);
            $table->boolean('email_notification')->default(false);
            $table->boolean('invisible')->default(false);
            $table->string('status', 16);
            $table->string('reset_token')->nullable();
            $table->timestamp('reset_token_expire')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('verify_token')->nullable();
            $table->timestamp('verify_token_expire')->nullable();
            $table->rememberToken();
            $table->timestamps();
            //$table->string('name')->nullable();
            //$table->string('last_name')->nullable();
            //$table->date('date_of_birth')->nullable();
            //$table->timestamp('email_verified_at')->nullable();
            //$table->boolean('phone_auth')->default(false);
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
