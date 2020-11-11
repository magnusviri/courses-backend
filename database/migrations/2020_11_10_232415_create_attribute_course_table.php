<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributeCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_course', function (Blueprint $table) {
            // $table->id();
            // $table->timestamps();
            // $table->primary(['attribute_id','course_id']);
            $table->unsignedBigInteger('attribute_id')->unsigned()->index();
            $table->foreign('attribute_id')->references('id')->on('attributes');
            $table->unsignedBigInteger('course_id')->unsigned()->index();
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
        Schema::dropIfExists('attribute_course');
    }
}
