<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('ttype')->default(1);
            $table->string('subject')->nullable();
            $table->string('image')->nullable();
            $table->boolean('add_participants')->default(0);
            $table->boolean('admin_call')->default(0);
            $table->boolean('send_message')->default(1);
            $table->boolean('lockout')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('threads');
    }
}
