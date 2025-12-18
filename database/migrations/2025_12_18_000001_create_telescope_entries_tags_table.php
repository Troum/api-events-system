<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telescope_entries_tags', function (Blueprint $table): void {
            $table->uuid('entry_uuid');
            $table->string('tag');

            $table->index('entry_uuid');
            $table->index('tag');
            $table->unique(['entry_uuid', 'tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telescope_entries_tags');
    }
};


