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
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    
    $table->enum('status', ['To Do', 'In Progress', 'Review', 'Revision', 'Done'])->default('To Do');
    $table->enum('priority', ['Low', 'Normal', 'High', 'Urgent'])->default('Normal');
    
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Dev
    $table->foreignId('qa_by')->nullable()->constrained('users')->nullOnDelete(); // QA
    
    $table->date('due_date')->nullable();
    $table->timestamp('completed_at')->nullable(); // Waktu pasti saat digeser ke Done
    
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
