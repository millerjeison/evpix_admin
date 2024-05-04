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
        Schema::table('events', function (Blueprint $table) {
            $table->renameColumn('date', 'start_date')->nullable();
            $table->text('description')->nullable()->change();
            $table->string('image')->after('title')->nullable();
            $table->string('end_date')->after('title')->nullable();
            $table->time('start_time')->nullable()->after('title');
            $table->time('end_time')->nullable()->after('title');
            $table->string('type')->after('title')->default('single');

        });
        Schema::table('web_settings', function (Blueprint $table) {
            $table->integer('status')->default(1)->after('image')->nullable();
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->integer('custom_role')->default(0)->after('guard_name');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->change();
            $table->dropColumn('type');
            $table->dropColumn('image');
            $table->dropColumn('end_date');
            $table->dropColumn('start_date');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
        });
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('custom_role');
        });
    }
};
