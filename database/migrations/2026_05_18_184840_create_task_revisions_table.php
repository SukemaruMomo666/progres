<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rejected_by')->constrained('users')->cascadeOnDelete(); // QA yang mereject
            
            $table->text('reason'); // Alasan spesifik kenapa task dikembalikan ke In Progress
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_revisions');
    }
};