<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('capacity');
            $table->unsignedSmallInteger('booked_count')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['date', 'start_time']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
