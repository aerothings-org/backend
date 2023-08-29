<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('swarm_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('packet_id');
            $table->integer('device_type');
            $table->integer('user_application_id');
            $table->integer('organization_id');
            $table->text('data');
            $table->integer('length');
            $table->integer('status');
            $table->string('hive_rx_time');
            $table->timestamps();
            $table->softDeletes();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swarm_messages');
    }
};
