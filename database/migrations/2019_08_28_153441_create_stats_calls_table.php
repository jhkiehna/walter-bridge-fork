<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatsCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!App::environment('production')) {
            Schema::connection('sqlite_stats_test')->dropIfExists('calls');
            Schema::connection('sqlite_stats_test')->create('calls', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('user_id')->unsigned();
                $table->boolean('valid');
                $table->bigInteger('areacode')->unsigned();
                $table->bigInteger('phone_number')->unsigned();
                $table->bigInteger('dialed_number')->unsigned();
                $table->boolean('international');
                $table->enum('type', ['Incoming', 'Outgoing', 'Transfer'])->nullable();
                $table->dateTime('date');
                $table->integer('duration')->unsigned();
                $table->string('raw')->nullable()->default(null);

                $table->timestamps();
                $table->softDeletes();
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
            Schema::connection('sqlite_stats_test')->dropIfExists('calls');
        }
    }
}
