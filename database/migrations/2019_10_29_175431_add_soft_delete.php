<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landing_datas', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE landing_datas MODIFY entry_status enum('new', 'archived', 'extracted', 'converted', 'trashed') NOT NULL;");
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
