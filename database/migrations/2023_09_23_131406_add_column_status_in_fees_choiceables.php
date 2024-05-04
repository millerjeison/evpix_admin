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
        Schema::table('fees_choiceables', function (Blueprint $table) {
            $table->bigInteger('status')->default('0')->after('payment_transaction_id')->comment('0 - not paid 1 - paid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fees_choiceables', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
