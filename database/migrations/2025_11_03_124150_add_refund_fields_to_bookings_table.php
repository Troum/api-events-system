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
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refund_requested', 'refunded'])
                ->default('pending')
                ->after('payment_gateway')
                ->comment('Статус бронирования');
            
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->timestamp('refund_requested_at')->nullable()->after('cancellation_reason');
            $table->timestamp('refunded_at')->nullable()->after('refund_requested_at');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'cancelled_at',
                'cancellation_reason',
                'refund_requested_at',
                'refunded_at',
                'refund_amount',
            ]);
        });
    }
};
