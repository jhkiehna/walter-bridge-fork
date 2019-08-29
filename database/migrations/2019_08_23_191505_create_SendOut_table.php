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
        Schema::connection('sqlite_walter_test')->dropIfExists('SendOut');
        Schema::connection('sqlite_walter_test')->create('SendOut', function (Blueprint $table) {
            $table->bigIncrements('soid');
            $table->dateTime('DateSent');
            $table->integer('Consultant');
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
        Schema::connection('sqlite_walter_test')->dropIfExists('SendOut');
    }
}
