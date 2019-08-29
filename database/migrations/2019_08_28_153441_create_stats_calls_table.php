<?php

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
        Schema::connection('sqlite_testing_stats')->dropIfExists('calls');
        Schema::connection('sqlite_testing_stats')->create('calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->boolean('valid');
            $table->bigInteger('dialed_number')->unsigned();
            $table->enum('type', ['Incoming', 'Outgoing', 'Transfer'])->nullable();
            $table->dateTime('date');
            $table->integer('duration')->unsigned();
            $table->string('raw')->nullable()->default(null);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('sqlite_testing_stats')->dropIfExists('calls');
    }
}
