<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('cat');
            $table->integer('sec');
            $table->string('com');
            $table->string('sub');
            $table->integer('num');
            $table->string('nam');
            $table->integer('enr');
            $table->string('des');
            $table->integer('cap');
            $table->string('typ');
            $table->string('uni');
            // $table->string('ins');
            // $table->string('att');
            $table->string('fee');
            // $table->string('gen');
            $table->integer('yea');
            $table->integer('sem');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
