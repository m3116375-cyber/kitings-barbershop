<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('slot_id')->constrained('slots')->restrictOnDelete();
            // menunggu | dikonfirmasi | sedang_dilayani | selesai | dibatalkan
            $table->string('status', 20)->default('menunggu')->index();
            $table->unsignedInteger('total')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('price');
            $table->timestamps();
        });

        Schema::create('booking_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('qty')->default(1);
            $table->unsignedInteger('price');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_products');
        Schema::dropIfExists('booking_services');
        Schema::dropIfExists('bookings');
    }
};
