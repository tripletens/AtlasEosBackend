<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowBuckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // `vendor_name`, `vendor_code`, `title`, `description`,
        // `img_url`, `status`, `created_at`,
        Schema::create('show_buck', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('img_url')->nullable();
            $table->boolean('status')->nullable();
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
        Schema::dropIfExists('show_buck');
    }
}
