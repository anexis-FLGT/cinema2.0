<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        Schema::table('bookings', function (Blueprint $table) use ($driver) {
            if (!Schema::hasColumn('bookings', 'payment_id')) {
                if ($driver !== 'sqlite') {
                    $table->string('payment_id')->nullable()->after('seat_id');
                } else {
                    $table->string('payment_id')->nullable();
                }
            }
            if (!Schema::hasColumn('bookings', 'payment_status')) {
                if ($driver !== 'sqlite') {
                    $table->enum('payment_status', ['ожидание', 'оплачено', 'отменено', 'ожидает_подтверждения'])->default('ожидание')->after('payment_id');
                } else {
                    $table->enum('payment_status', ['ожидание', 'оплачено', 'отменено', 'ожидает_подтверждения'])->default('ожидание');
                }
            }
            if (!Schema::hasColumn('bookings', 'amount')) {
                if ($driver !== 'sqlite') {
                    $table->decimal('amount', 10, 2)->nullable()->after('payment_status');
                } else {
                    $table->decimal('amount', 10, 2)->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_id', 'payment_status', 'amount']);
        });
    }
};

