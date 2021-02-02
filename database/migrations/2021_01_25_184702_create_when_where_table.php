<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhenWhereTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('when_where', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('day')->nullable();
            $table->string('tim')->nullable();
            $table->string('loc')->nullable();
            $table->string('tba')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('when_where');
    }
}
