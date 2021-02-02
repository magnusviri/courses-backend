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

            $table->integer('cap');
            $table->integer('cat');
            $table->string('com');
            $table->string('nam');
            $table->integer('num');
            $table->string('sec');
            $table->integer('sem');
            $table->string('sub');
            $table->string('typ');
            $table->string('uni');
            $table->integer('yea');
            $table->integer('enr')->nullable();
            $table->string('fee')->nullable();
            $table->string('rek')->nullable();
            $table->string('req')->nullable();
            $table->string('sea')->nullable();
            $table->string('syl')->nullable();
            $table->string('tba')->nullable();
            $table->string('wai')->nullable();
            $table->bigInteger('description_id')->unsigned()->index()->nullable();
            $table->bigInteger('special_id')->unsigned()->index()->nullable();
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
