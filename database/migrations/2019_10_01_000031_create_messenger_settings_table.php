<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessengerSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messenger_settings', function (Blueprint $table) {
            $table->uuid('owner_id');
            $table->string('owner_type');
            $table->boolean('message_popups')->default(1);
            $table->boolean('message_sound')->default(1);
            $table->boolean('call_ringtone_sound')->default(1);
            $table->boolean('knoks')->default(1);
            $table->boolean('calls_outside_networks')->default(0);
            $table->integer('online_status')->default(1);
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
        Schema::dropIfExists('messenger_settings');
    }
}
