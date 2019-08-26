<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SendOut', function (Blueprint $table) {
            $table->bigIncrements('soid');
            $table->integer('soType');
            $table->dateTime('DateCreated');
            $table->dateTime('DateSent');
            $table->boolean('firstResume');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('SendOut');
    }
}
