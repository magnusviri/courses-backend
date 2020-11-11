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
            $table->longText('des');
            $table->integer('cap');
            $table->string('typ');
            $table->string('uni');
            $table->integer('yea');
            $table->integer('sem');
            $table->string('fee')->nullable();
            $table->string('rek')->nullable();
            $table->string('syl')->nullable();
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
