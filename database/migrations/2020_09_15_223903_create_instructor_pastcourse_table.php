<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstructorPastcourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intructor_pastcourse', function (Blueprint $table) {
            $table->id();

//   `instructor_id` int(10) unsigned NOT NULL,
//   `course_taught_id` int(10) unsigned NOT NULL

            $table->integer('instructor_id');
            $table->integer('pastcourse_id');

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
        Schema::dropIfExists('intructor_pastcourse');
    }
}
