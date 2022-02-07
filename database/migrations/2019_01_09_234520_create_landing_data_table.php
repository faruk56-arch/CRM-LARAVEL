<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandingDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landing_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landing_id');
            $table->foreign('landing_id')->references('id')->on('landings');
            $table->text('data');
            $table->ipAddress('visitor');
            $table->enum('entry_status', ['new', 'archived', 'extracted'])->default('new');
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
        Schema::dropIfExists('landing_data');
    }
}
