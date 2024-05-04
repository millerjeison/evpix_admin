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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('type', 128)->comment('text,number,textarea,dropdown,checkbox,radio,fileupload');
            $table->tinyInteger('is_required')->default(0);
            $table->text('default_values')->comment('values of radio,checkbox,dropdown,etc')->nullable();
            $table->text('other')->comment('extra HTML attributes')->nullable();
            $table->integer('rank')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('students', static function (Blueprint $table) {
            $table->text('dynamic_fields')->after('guardian_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_fields');
        Schema::table('students', static function (Blueprint $table) {
            $table->dropColumn('dynamic_fields');
        });
    }
};
