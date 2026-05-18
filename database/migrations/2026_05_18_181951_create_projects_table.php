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
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique(); // Untuk keamanan URL (megah & rahasia)
    $table->string('name');
    $table->foreignId('client_id')->constrained()->restrictOnDelete(); // Restrict: tidak bisa hapus klien jika ada proyeknya
    $table->foreignId('period_id')->constrained()->restrictOnDelete();
    $table->foreignId('pic_id')->constrained('users')->restrictOnDelete();
    $table->foreignId('finder_id')->constrained('users')->restrictOnDelete(); // Untuk hitung KPI pencari mitra
    
    // Logika Finansial
    $table->decimal('total_price', 15, 2);
    $table->decimal('dp_amount', 15, 2)->default(0);
    $table->enum('payment_status', ['Unpaid', 'DP Paid', 'Fully Paid'])->default('Unpaid');
    
    // Logika Operasional
    $table->date('start_date');
    $table->date('deadline');
    $table->enum('status', ['Planning', 'In Progress', 'Testing', 'Completed', 'Cancelled'])->default('Planning');
    
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
