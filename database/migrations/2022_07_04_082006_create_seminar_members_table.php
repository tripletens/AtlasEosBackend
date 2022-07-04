<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeminarMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 'seminar_id', 'dealer_id', 'bookmark_status', 'current_seminar_status',
        // 'status', 'created_at', 'updated_at', 'deleted_at'
        Schema::create('seminar_members', function (Blueprint $table) {
            $table->id();
            $table->integer('seminar_id')->nullable();
            $table->integer('dealer_id')->nullable();
            $table->boolean('bookmark_status')->default(true);
            $table->integer('current_seminar_status')->default(1); # 1 means scheduled  2 means ongoing 3 means watched
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('seminar_members');
    }
}
