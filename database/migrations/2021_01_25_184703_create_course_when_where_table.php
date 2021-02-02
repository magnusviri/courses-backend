<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseWhenWhereTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_when_where', function (Blueprint $table) {
//             $table->id();
//             $table->timestamps();
            $table->bigInteger('course_id')->unsigned()->index();
            $table->foreign('course_id')->references('id')->on('courses');
            $table->bigInteger('when_where_id')->unsigned()->index();
            $table->foreign('when_where_id')->references('id')->on('when_where');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_when_where');
    }
}
