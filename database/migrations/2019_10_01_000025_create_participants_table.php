<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('thread_id');
            $table->uuid('owner_id');
            $table->string('owner_type');
            $table->boolean('admin')->default(0);
            $table->timestamp('last_read')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
            $table->primary('id');
            $table->foreign('thread_id')->references('id')->on('threads')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participants');
    }
}
