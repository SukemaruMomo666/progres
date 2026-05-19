<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Menyimpan Kartu Tugas Baru ke dalam Proyek
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'priority'    => 'required|in:Low,Medium,High,Urgent',
            'deadline'    => 'nullable|date',
            // Default status saat tugas baru dibuat biasanya "To Do" atau "Backlog"
            'status'      => 'nullable|string', 
        ]);

        DB::transaction(function () use ($validated) {
            $task = Task::create([
                'project_id'  => $validated['project_id'],
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'assignee_id' => $validated['assignee_id'],
                'priority'    => $validated['priority'],
                'deadline'    => $validated['deadline'],
                'status'      => $validated['status'] ?? 'To Do', 
            ]);

            // JEJAK DIGITAL: Catat Pembuatan Tugas
            ActivityLog::record(
                'Tambah Tugas', 
                "Membuat kartu tugas baru '{$task->title}' pada proyek '{$task->project->name}'."
            );
        });

        return back()->with('success', 'Kartu tugas berhasil ditambahkan ke papan kerja.');
    }

    /**
     * Memperbarui Detail Kartu Tugas (Judul, Deskripsi, Assignee, Priority)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'priority'    => 'required|in:Low,Medium,High,Urgent',
            'deadline'    => 'nullable|date',
            'status'      => 'required|string',
        ]);

        $task = Task::findOrFail($id);

        DB::transaction(function () use ($task, $validated) {
            $oldTitle = $task->title;
            
            $task->update([
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'assignee_id' => $validated['assignee_id'],
                'priority'    => $validated['priority'],
                'deadline'    => $validated['deadline'],
                'status'      => $validated['status'],
            ]);

            // JEJAK DIGITAL: Catat Perubahan Tugas
            ActivityLog::record(
                'Edit Tugas', 
                "Memperbarui detail tugas '{$oldTitle}' di proyek '{$task->project->name}'."
            );
        });

        return back()->with('success', 'Detail tugas berhasil diperbarui.');
    }

    /**
     * Menghapus Kartu Tugas dari Papan Proyek
     */
    public function destroy($id)
    {
        // Proteksi Otoritas: Hanya Role Founder, Co-Founder, dan HR yang boleh HAPUS tugas
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Hanya Manajemen yang diizinkan untuk menghapus kartu tugas.');
        }

        $task = Task::with('project')->findOrFail($id);
        
        DB::transaction(function () use ($task) {
            $taskTitle = $task->title;
            $projectName = $task->project->name ?? 'Unknown Project';

            // Jika tugas memiliki lampiran, hapus juga lampirannya agar tidak menjadi file sampah
            if (method_exists($task, 'attachments')) {
                foreach ($task->attachments as $attachment) {
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
                    }
                }
                $task->attachments()->delete();
            }

            // Hapus kartu tugas
            $task->delete();

            // JEJAK DIGITAL: Catat Penghapusan Tugas
            ActivityLog::record(
                'Hapus Tugas', 
                "Menghapus kartu tugas '{$taskTitle}' dari proyek '{$projectName}' secara permanen."
            );
        });

        return back()->with('success', 'Kartu tugas berhasil dihapus beserta seluruh lampirannya.');
    }

    // =========================================================================
    // FUNGSI BAWAAN (Dikosongkan karena menggunakan Modal/Livewire)
    // =========================================================================
    public function index() { }
    public function create() { }
    public function show(Task $task) { }
    public function edit(Task $task) { }
}