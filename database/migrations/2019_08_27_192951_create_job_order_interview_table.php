<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOrderInterviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('sqlite_walter_test')->dropIfExists('jobOrder_interview');
        Schema::connection('sqlite_walter_test')->create('jobOrder_interview', function (Blueprint $table) {
            $table->bigIncrements('intID');
            $table->dateTime('dateCreated');
            $table->integer('consultant');

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
        Schema::connection('sqlite_walter_test')->dropIfExists('jobOrder_interview');
    }
}
