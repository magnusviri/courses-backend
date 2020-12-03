<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttrCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attr_course', function (Blueprint $table) {
            // $table->id();
            // $table->timestamps();
            // $table->primary(['attr_id','course_id']);
            $table->bigInteger('attr_id')->unsigned()->index();
            $table->foreign('attr_id')->references('id')->on('attrs');
            $table->bigInteger('course_id')->unsigned()->index();
            $table->foreign('course_id')->references('id')->on('courses');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attr_course');
    }
}
