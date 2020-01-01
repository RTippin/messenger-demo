<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThreadInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thread_invites', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('thread_id');
            $table->uuid('owner_id');
            $table->string('owner_type');
            $table->string('slug')->unique();
            $table->integer('max_use')->default(0);
            $table->integer('uses')->default(0);
            $table->timestamp('expires_at')->nullable()->default(null);
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
        Schema::dropIfExists('thread_invites');
    }
}
