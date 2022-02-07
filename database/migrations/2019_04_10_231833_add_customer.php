<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('exports', function (Blueprint $table) {
            $table->unsignedInteger('count');
            $table->unsignedInteger('customer_id');
        });

        Schema::create('customers_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landing_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('leads_number');
            $table->enum('frequency', array('day', 'week'));
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
        //
    }
}
