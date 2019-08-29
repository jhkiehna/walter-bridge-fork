<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonCodeDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('sqlite_walter_test')->dropIfExists('person_codeDate');
        Schema::connection('sqlite_walter_test')->create('person_codeDate', function (Blueprint $table) {
            $table->bigIncrements('cdid');
            $table->dateTime('dateCoded');
            $table->integer('consultant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('sqlite_walter_test')->dropIfExists('person_codeDate');
    }
}
