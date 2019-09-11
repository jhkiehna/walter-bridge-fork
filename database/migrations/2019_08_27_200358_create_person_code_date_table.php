<?php

use Illuminate\Support\Facades\App;
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
        if (!App::environment('production')) {
            Schema::connection('sqlite_walter_test')->dropIfExists('person_codeDate');
            Schema::connection('sqlite_walter_test')->create('person_codeDate', function (Blueprint $table) {
                $table->bigIncrements('cdid');
                $table->dateTime('dateCoded');
                $table->integer('consultant');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!App::environment('production')) {
            Schema::connection('sqlite_walter_test')->dropIfExists('person_codeDate');
        }
    }
}
