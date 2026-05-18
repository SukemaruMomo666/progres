<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete(); // Dev yang upload
            
            // File Paths
            $table->string('ui_screenshot_path')->nullable();
            $table->string('repo_push_path')->nullable();
            
            $table->text('dev_notes')->nullable(); // Catatan tambahan dari Dev untuk QA
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_proofs');
    }
};