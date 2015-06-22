<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('names_id')->unsigned();
            $table->foreign('names_id')->references('id')->on('properties_names')->onDelete('cascade');
            $table->integer('modification_id')->unsigned();
            $table->foreign('modification_id')->references('id')->on('modifications')->onDelete('cascade');
            $table->text('value');
            $table->unique(['names_id', 'modification_id']);
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
        Schema::dropIfExists('properties');
    }
}
