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
        Schema::create('hse_records', function (Blueprint $table) {
            $table->id();
            $table->string('timestamp')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('kwh')->nullable();
            $table->string('consumed')->nullable();
            $table->string('jam_pencatatan')->nullable();
            $table->text('picture')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hse_records');
    }
};
