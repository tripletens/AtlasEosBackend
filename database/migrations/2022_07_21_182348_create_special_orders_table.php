<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // quantity,vendor_id and description
        Schema::create('special_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('vendor_id')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('special_orders');
    }
}
