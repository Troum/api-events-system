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
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_gateway', ['yookassa', 'stripe', 'paypal', 'webpay', 'pay_on_arrival'])
                ->nullable()
                ->default('pay_on_arrival')
                ->after('payment_status')
                ->comment('Платежная система или оплата по факту');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_gateway');
        });
    }
};
