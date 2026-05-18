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
Schema::create('periods', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // e.g., "Kuartal 1 - 2026"
    $table->date('start_date');
    $table->date('end_date');
    $table->integer('target_mitra_per_user')->default(1); // Target KPI
    $table->boolean('is_active')->default(false); // Hanya 1 yang boleh true
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
