<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePastCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pastcourse', function (Blueprint $table) {
            $table->id();

            $table->integer('course_id');
            $table->integer('year_id');
            $table->integer('semester_id');
            $table->string('section');
            $table->integer('enrollment');
            $table->string('evaluation_url');
            $table->string('syllabus_id');

//   `course_id` int(11) unsigned NOT NULL,
//   `year_id` int(10) unsigned NOT NULL,
//   `semester_id` int(10) unsigned NOT NULL,
//   `section` varchar(6) NOT NULL,
//   `enrollment` int(11) NOT NULL

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pastcourse');
    }
}
