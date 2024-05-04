<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_teachers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('class_section_id')->nullable(true)->unsigned();
            $table->foreign('class_section_id')->references('id')->on('class_sections')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('class_teacher_id')->nullable(true)->unsigned();
            $table->foreign('class_teacher_id')->references('id')->on('teachers')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_teachers');
    }
};
