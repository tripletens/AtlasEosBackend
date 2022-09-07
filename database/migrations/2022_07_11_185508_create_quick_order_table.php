<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // `id`, `uid`, `dealer`, `vendor`, `atlas_id`, `product_id`, `qty`,
        // `price`, `unit_price`, `status`, `created_at`, `updated_at`
        Schema::create('quick_order', function (Blueprint $table) {
            // $table->id();
            // $table->string('uid')->nullable();
            // $table->string('dealer')->nullable();
            // $table->string('vendor')->nullable();
            // $table->string('atlas_id')->nullable();
            // $table->string('product_id')->nullable();
            // $table->string('groupings')->nullable();
            // $table->integer('qty')->nullable();
            // $table->integer('price')->nullable();
            // $table->integer('unit_price')->nullable();
            // $table->boolean('status')->default(true);
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quick_order');
    }
}
