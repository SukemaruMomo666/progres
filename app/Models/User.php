<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** * Mengaktifkan Fitur Pendukung Pabrikasi Data, Notifikasi, 
     * Penghapusan Lunak (Soft Delete), dan Hak Akses Spatie
     */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * ZONA AMAN (Mass Assignment): Daftar kolom yang diizinkan untuk diisi secara massal
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    /**
     * ZONA PRIVASI: Menyembunyikan atribut sensitif saat model dikonversi ke Array atau JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ZONA CASTING: Otomatisasi konversi tipe data database ke tipe data native PHP/Laravel
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean', // Memastikan nilai 1/0 di database dibaca sebagai true/false di aplikasi
        ];
    }

    /* |--------------------------------------------------------------------------
    | RELASI DATABASE (LOGIKA DEWA)
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi: Proyek di mana user ini bertindak sebagai PIC (Penanggung Jawab)
     */
    public function projectsAsPic(): HasMany
    {
        return $this->hasMany(Project::class, 'pic_id');
    }

    /**
     * Relasi: Proyek di mana user ini bertindak sebagai Finder (Pencari Mitra / Kontributor KPI)
     */
    public function projectsAsFinder(): HasMany
    {
        return $this->hasMany(Project::class, 'finder_id');
    }

    /**
     * Relasi: Task (Kanban Sticky Notes) yang ditugaskan ke user ini (sebagai Developer)
     */
    public function tasksAssigned(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Relasi: Task yang sedang diawasi/ditinjau oleh user ini (sebagai Quality Assurance / PM)
     */
    public function tasksQa(): HasMany
    {
        return $this->hasMany(Task::class, 'qa_by');
    }

    /**
     * Relasi: Bukti kerja (Screenshot UI & Push Repo) yang diunggah oleh user ini
     */
    public function taskProofs(): HasMany
    {
        return $this->hasMany(TaskProof::class, 'submitted_by');
    }

    /**
     * Relasi: Daftar instruksi penolakan/revisi tugas yang pernah dikeluarkan oleh user ini
     */
    public function taskRevisions(): HasMany
    {
        return $this->hasMany(TaskRevision::class, 'rejected_by');
    }

    /**
     * Relasi: Data kas keuangan yang dicatat/diinput oleh user ini ke dalam Buku Kas
     */
    public function financesRecorded(): HasMany
    {
        return $this->hasMany(Finance::class, 'recorded_by');
    }
}