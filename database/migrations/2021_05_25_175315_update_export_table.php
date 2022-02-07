<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateExportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_landings', function (Blueprint $table) {

            $table->unsignedInteger('landing_id');

            $table->unsignedInteger('export_id');
        });

        foreach (\App\Exports::all() as $item) {
            $item->landings()->attach([$item->landing_id]);
        }

        Schema::table('exports', function (Blueprint $table) {
            $table->boolean('big_export')->default(false);
            $table->unsignedInteger('landing_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
