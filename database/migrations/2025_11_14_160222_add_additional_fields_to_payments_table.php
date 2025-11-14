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
        Schema::table('payments', function (Blueprint $table) {
            // Обновляем enum статусов для поддержки новых статусов ЮKassa
            $table->string('status')->default('pending')->change();

            // Обновляем enum провайдеров
            $table->string('provider')->change();

            // Добавляем новые поля
            $table->json('metadata')->nullable()->after('transaction_id');
            $table->string('confirmation_type')->nullable()->after('metadata');
            $table->string('refund_id')->nullable()->after('confirmation_type');
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('refund_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['metadata', 'confirmation_type', 'refund_id', 'refunded_amount']);
        });
    }
};
