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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('id_payment');
            $table->string('payment_id')->nullable()->comment('ID платежа от ЮKassa');
            $table->enum('payment_status', ['ожидание', 'оплачено', 'отменено', 'ожидает_подтверждения'])->default('ожидание');
            $table->decimal('amount', 10, 2)->nullable();
            $table->foreignId('booking_id')->constrained('bookings', 'id_booking')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('payment_id');
            $table->index('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
