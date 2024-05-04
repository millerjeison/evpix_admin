<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_room_id')->nullable(true)->unsigned();
            $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('user_id')->nullable(true)->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->morphs('modal');
            $table->bigInteger('sender_id')->nullable(true)->unsigned();
            $table->foreign('sender_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->text('body')->nullable();
            $table->dateTime('date')->nullable()->default(now());
            $table->timestamps();
        });

        Schema::create('chat_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id')->nullable(true)->unsigned();
            $table->foreign('message_id')->references('id')->on('chat_messages')->onUpdate('cascade')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_type');
            $table->timestamps();
        });
        Schema::create('read_messages', function (Blueprint $table) {
            $table->id();
            $table->morphs('modal');
            $table->bigInteger('user_id')->nullable(true)->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('last_read_message_id')->nullable(true)->unsigned();
            $table->foreign('last_read_message_id')->references('id')->on('chat_messages')->onUpdate('cascade')->onDelete('cascade');
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('chat_rooms');
        Schema::dropIfExists('chat_members');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_files');
        Schema::dropIfExists('read_messages');
    }
};
