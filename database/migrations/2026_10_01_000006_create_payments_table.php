<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            // midtrans | cod (null = pelanggan belum memilih)
            $table->string('method', 20)->nullable();
            // belum_dibayar | lunas | gagal | kedaluwarsa | dibatalkan
            $table->string('status', 20)->default('belum_dibayar')->index();
            $table->unsignedInteger('amount');
            $table->string('midtrans_order_id', 64)->nullable()->index();
            $table->text('snap_token')->nullable();
            $table->timestamp('snap_created_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
