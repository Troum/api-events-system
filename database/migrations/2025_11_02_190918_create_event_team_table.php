<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $table->string('role_in_event')->nullable(); // Главный тренер, Ассистент
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['event_id', 'team_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_team');
    }
};
