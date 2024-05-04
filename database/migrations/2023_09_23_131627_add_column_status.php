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
        Schema::table('paid_installment_fees', function (Blueprint $table) {
            $table->bigInteger('status')->default('0')->after('payment_transaction_id')->comment('0 - not paid 1 - paid')->nullable();
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->bigInteger('status')->default('0')->after('remark')->comment('0 - not send 1 - send')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paid_installment_fees', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
