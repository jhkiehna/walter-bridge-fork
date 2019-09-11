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
            $table->bigInteger('intranet_user_id')->unsigned();
            $table->bigInteger('stats_call_id')->unsigned();

            $table->boolean('valid');
            $table->bigInteger('dialed_number')->unsigned();
            $table->boolean('international');
            $table->enum('type', ['Incoming', 'Outgoing', 'Transfer'])->nullable();

            $table->integer('duration')->unsigned();
            $table->dateTime('date');

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
