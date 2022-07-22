<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerIdToSpecialOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('special_orders', function (Blueprint $table) {
            // add the dealer_id to the table
            $table->string('dealer_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('special_orders', function (Blueprint $table) {
            // drop the column dealer_id
            $table->dropColumn('dealer_id');
        });
    }
}
