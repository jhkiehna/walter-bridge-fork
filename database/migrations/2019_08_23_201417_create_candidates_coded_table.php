<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCandidatesCodedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidates_coded', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('central_id')->unsigned();
            $table->bigInteger('walter_consultant_id')->unsigned();
            $table->bigInteger('walter_coded_id')->unsigned();
            $table->dateTime('date');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('central_id')->references('central_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidates_coded');
    }
}
