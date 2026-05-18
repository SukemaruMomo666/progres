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
Schema::create('finances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('period_id')->constrained()->restrictOnDelete(); // Mengikat uang ke periode tertentu
    $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete(); // Boleh null jika ini uang operasional (bukan proyek)
    
    $table->enum('type', ['Income', 'Expense']); // Masuk / Keluar
    $table->string('category'); // e.g., "DP Proyek", "Pelunasan", "Sewa Server", "Gaji", "Aset"
    $table->decimal('amount', 15, 2);
    $table->string('description'); // e.g., "Pembayaran DP dari klien PT ABC"
    
    $table->foreignId('recorded_by')->constrained('users'); // Siapa HR/Founder yang mencatat ini
    $table->date('transaction_date');
    
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finances');
    }
};
