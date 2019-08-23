<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('central_id')->unsigned();

            $table->integer('extension')->unsigned();
            $table->integer('trunk')->unsigned()->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->boolean('valid');
            $table->enum('type', ['Incoming', 'Outgoing', 'Transfer'])->nullable();
            $table->dateTime('date');
            $table->integer('duration')->unsigned();

            $table->integer('areacode')->unsigned()->nullable();
            $table->integer('phone_number')->unsigned()->nullable();
            $table->bigInteger('dialed_number')->unsigned();

            $table->string('city')->nullable();
            $table->string('state')->nullable();

            $table->boolean('incoming');
            $table->boolean('long_distance');
            $table->boolean('international');
            $table->boolean('local');

            $table->integer('department_id')->nullable();
            $table->string('department')->nullable();

            $table->string('raw');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('central_id')->references('central_id')->on('users');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calls');
    }
}
