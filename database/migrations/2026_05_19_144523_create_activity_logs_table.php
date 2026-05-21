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
    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('activity'); // Contoh: 'Menghapus Proyek', 'Reset Password'
        $table->text('description')->nullable(); // Detail: 'Menghapus proyek Aplikasi EWS RADAR'
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable(); // Browser / Device yang digunakan
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
