<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseMeetsWithTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_meets_with', function (Blueprint $table) {
//             $table->id();
//             $table->timestamps();
            $table->bigInteger('course_id')->unsigned()->index();
            $table->foreign('course_id')->references('id')->on('courses');
            $table->bigInteger('meets_with_id')->unsigned()->index();
            $table->foreign('meets_with_id')->references('id')->on('meets_with');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_meets_with');
    }
}
