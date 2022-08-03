<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfUrlToShowBuckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('show_buck', function (Blueprint $table) {
            // add pdf_url for show buck
            $table->string('pdf_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('show_buck', function (Blueprint $table) {
            // delete the show buck 
            $table->dropColumn('pdf_url');
        });
    }
}
