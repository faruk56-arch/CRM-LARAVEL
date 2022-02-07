<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToLandingDataAndExport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landing_datas', function (Blueprint $table) {
            $table->string('import_from')->nullable();
        });

        Schema::table('exports', function (Blueprint $table) {
            $table->string('for_customer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('landing_data_and_export', function (Blueprint $table) {
            //
        });
    }
}
