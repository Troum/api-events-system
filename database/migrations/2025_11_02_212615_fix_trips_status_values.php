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
        // Обновляем все записи со статусом 'active' на 'published'
        DB::table('trips')
            ->where('status', 'active')
            ->update(['status' => 'published']);
        
        // Обновляем все записи со статусом 'scheduled' на 'published'
        DB::table('trips')
            ->where('status', 'scheduled')
            ->update(['status' => 'published']);
        
        // Обновляем все записи со статусом 'departed' на 'published'
        DB::table('trips')
            ->where('status', 'departed')
            ->update(['status' => 'published']);
        
        // Обновляем все записи со статусом 'arrived' на 'completed'
        DB::table('trips')
            ->where('status', 'arrived')
            ->update(['status' => 'completed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Откатываем изменения
        DB::table('trips')
            ->where('status', 'published')
            ->update(['status' => 'active']);
    }
};
