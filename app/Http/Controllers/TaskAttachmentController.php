<?php

namespace App\Http\Controllers;

use App\Models\TaskAttachment;
use App\Models\Task;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    /**
     * Menyimpan dan Mengunggah Lampiran Baru
     */
    public function store(Request $request)
    {
        // Validasi input: wajib ada task_id dan file dengan maksimal ukuran 10MB
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'file'    => 'required|file|max:10240', // max 10MB
        ]);

        $task = Task::findOrFail($request->task_id);

        DB::transaction(function () use ($request, $task) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            
            // Simpan file fisik ke folder storage/app/public/task-attachments
            $path = $file->store('task-attachments', 'public');

            // Simpan data logikanya ke database
            TaskAttachment::create([
                'task_id'   => $task->id,
                'user_id'   => auth()->id(), // Mencatat siapa yang nge-upload
                'file_name' => $originalName,
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
            ]);

            // JEJAK DIGITAL: Catat ke Audit Log
            ActivityLog::record(
                'Upload Lampiran', 
                "Mengunggah file '{$originalName}' ke kartu tugas (Task ID: #{$task->id})."
            );
        });

        return back()->with('success', 'Lampiran tugas berhasil diunggah.');
    }

    /**
     * Menghapus Lampiran (Database & File Fisik)
     */
    public function destroy($id)
    {
        $attachment = TaskAttachment::findOrFail($id);

        // PROTEKSI GANDA: Hanya uploader asli ATAU manajemen elit yang boleh menghapus file ini
        $isOwner = auth()->id() === $attachment->user_id;
        $isAdmin = auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR']);

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Anda tidak diizinkan menghapus lampiran milik orang lain.');
        }

        DB::transaction(function () use ($attachment) {
            $fileName = $attachment->file_name;
            
            // 1. Hapus file fisiknya dari folder server agar tidak menumpuk jadi sampah
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            // 2. Hapus datanya dari database
            $attachment->delete();

            // 3. JEJAK DIGITAL: Catat ke Audit Log
            ActivityLog::record(
                'Hapus Lampiran', 
                "Menghapus lampiran file '{$fileName}' secara permanen dari server."
            );
        });

        return back()->with('success', 'Lampiran berhasil dihapus dari sistem.');
    }

    // =========================================================================
    // FUNGSI DI BAWAH INI DIBIARKAN KOSONG KARENA BIASANYA TIDAK DIPAKAI
    // Lampiran umumnya langsung di-upload/dihapus lewat Modal di Kanban Board
    // =========================================================================
    public function index() { }
    public function create() { }
    public function show(TaskAttachment $taskAttachment) { }
    public function edit(TaskAttachment $taskAttachment) { }
    public function update(Request $request, TaskAttachment $taskAttachment) { }
}