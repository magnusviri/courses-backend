<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course', function (Blueprint $table) {
            $table->id();

//   `course_number` varchar(10) NOT NULL,
//   `course_name` varchar(50) NOT NULL,
//   `gen_ed` varchar(20) NOT NULL

            $table->string('course_number');
            $table->string('course_name')->index();
            $table->string('gen_ed');

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
        Schema::dropIfExists('course');
    }
}
