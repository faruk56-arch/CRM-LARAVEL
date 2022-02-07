<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryToProduction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->enum('category', ['pac', 'pv', 'other'])->default('pac');
        });
        $region = '["FR-28","FR-29","FR-22","FR-23","FR-21","FR-26","FR-27","FR-24","FR-25","FR-MQ","FR-94","FR-93","FR-92","FR-91","FR-90","FR-17","FR-16","FR-15","FR-14","FR-13","FR-12","FR-11","FR-10","FR-2B","FR-2A","FR-19","FR-18","FR-88","FR-89","FR-80","FR-81","FR-82","FR-83","FR-84","FR-85","FR-86","FR-87","FR-01","FR-02","FR-03","FR-04","FR-05","FR-06","FR-07","FR-08","FR-09","FR-RE","FR-75","FR-74","FR-77","FR-76","FR-71","FR-70","FR-73","FR-72","FR-79","FR-78","FR-YT","FR-66","FR-67","FR-64","FR-65","FR-62","FR-63","FR-60","FR-61","FR-68","FR-69","FR-53","FR-52","FR-51","FR-50","FR-57","FR-56","FR-55","FR-54","FR-59","FR-58","FR-48","FR-49","FR-44","FR-45","FR-46","FR-47","FR-40","FR-41","FR-42","FR-43","FR-95","FR-GF","FR-GP","FR-39","FR-38","FR-31","FR-30","FR-33","FR-32","FR-35","FR-34","FR-37","FR-36"]';
        $region = json_decode($region);

        $arr = [];
        foreach ($region as $el) {
            $arr[] = ['name' => $el, 'region' => $el, 'category' => 'pv'];
        }

        \Illuminate\Support\Facades\DB::table('productions')->insert($arr);

        $arr = [];
        foreach ($region as $el) {
            $arr[] = ['name' => $el, 'region' => $el, 'category' => 'other'];
        }

        \Illuminate\Support\Facades\DB::table('productions')->insert($arr);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production', function (Blueprint $table) {
            //
        });
    }
}
