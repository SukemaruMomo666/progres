<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Tambahan Wajib
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password', 'phone', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles; // Sisipkan SoftDeletes dan HasRoles di sini

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean', // Pastikan is_active dibaca sebagai true/false
        ];
    }

    /* 
    |--------------------------------------------------------------------------
    | RELASI DATABASE (LOGIKA DEWA)
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi: Proyek di mana user ini menjadi PIC (Penanggung Jawab)
     */
    public function projectsAsPic(): HasMany
    {
        return $this->hasMany(Project::class, 'pic_id');
    }

    /**
     * Relasi: Proyek di mana user ini menjadi Finder (Pencari Mitra / Pencetak KPI)
     */
    public function projectsAsFinder(): HasMany
    {
        return $this->hasMany(Project::class, 'finder_id');
    }

    /**
     * Relasi: Task (Kanban) yang ditugaskan ke user ini (sebagai Dev)
     */
    public function tasksAssigned(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Relasi: Task yang sedang diawasi/direview oleh user ini (sebagai QA)
     */
    public function tasksQa(): HasMany
    {
        return $this->hasMany(Task::class, 'qa_by');
    }

    /**
     * Relasi: Bukti kerja (SS UI & Repo) yang pernah diupload oleh user ini (Dev)
     */
    public function taskProofs(): HasMany
    {
        return $this->hasMany(TaskProof::class, 'submitted_by');
    }

    /**
     * Relasi: Daftar penolakan/revisi yang pernah dikeluarkan oleh user ini (QA)
     */
    public function taskRevisions(): HasMany
    {
        return $this->hasMany(TaskRevision::class, 'rejected_by');
    }

    /**
     * Relasi: Data keuangan yang dicatat oleh user ini (Founder/Co-Founder/HR)
     */
    public function financesRecorded(): HasMany
    {
        return $this->hasMany(Finance::class, 'recorded_by');
    }
}