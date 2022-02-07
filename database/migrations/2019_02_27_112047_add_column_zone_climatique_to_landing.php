<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnZoneClimatiqueToLanding extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landing', function (Blueprint $table) {
            Schema::table('landings', function (Blueprint $table) {
                $table->boolean('zone_climatique')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('landing', function (Blueprint $table) {
            //
        });
    }
}
