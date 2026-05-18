<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskProof;
use Illuminate\Support\Facades\Auth;

class KanbanBoard extends Component
{
    use WithFileUploads;

    public Project $project;
    
    // State untuk Modal Detail Task
    public $selectedTask = null;
    public $showModal = false;
    
    // Form Inputs untuk Bukti Kerja (Dev)
    public $uiScreenshot;
    public $repoPush;
    public $devNotes;

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    /**
     * LOGIKA DEWA: Sinkronisasi Drag & Drop dengan Validasi Ketat
     */
    public function updateTaskStatus($taskId, $newStatus)
    {
        $task = Task::find($taskId);
        
        if (!$task) return;

        // HARD-BLOCK LOGIC: Dev mau geser ke 'Review' tapi belum upload bukti? Blokir!
        if ($newStatus === 'Review') {
            $hasProof = TaskProof::where('task_id', $taskId)->exists();
            if (!$hasProof) {
                return; 
            }
        }

        // VALIDASI QA: Hanya QA & Founder yang bisa geser kartu ke 'Done'
        if ($newStatus === 'Done' && !Auth::user()->hasAnyRole(['Founder', 'Co-Founder', 'QA'])) {
            return;
        }

        // Jika lolos semua validasi, update status di database
        $task->update(['status' => $newStatus]);
        
        // Rekam timestamp jika selesai
        if ($newStatus === 'Done') {
            $task->update(['completed_at' => now()]);
        }
    }

    /**
     * Buka Modal Detail & Ambil Data Terkait
     */
    public function openTaskDetail($taskId)
    {
        $this->selectedTask = Task::with(['proofs', 'revisions'])->find($taskId);
        $this->showModal = true;
    }

    /**
     * Simpan Bukti Kerja Developer (SS UI & Repo)
     */
    public function submitProof()
    {
        $this->validate([
            'uiScreenshot' => 'required|image|max:2048', // Max 2MB
            'repoPush' => 'required|image|max:2048',
            'devNotes' => 'nullable|string',
        ]);

        // Simpan file ke storage internal xgrow
        $uiPath = $this->uiScreenshot->store('proofs/ui', 'public');
        $repoPath = $this->repoPush->store('proofs/repo', 'public');

        TaskProof::create([
            'task_id' => $this->selectedTask->id,
            'submitted_by' => Auth::id(),
            'ui_screenshot_path' => $uiPath,
            'repo_push_path' => $repoPath,
            'dev_notes' => $this->devNotes,
        ]);

        // Otomatis ubah status ke Review setelah bukti diunggah
        $this->selectedTask->update(['status' => 'Review']);

        $this->showModal = false;
        $this->reset(['uiScreenshot', 'repoPush', 'devNotes']);
    }

    public function render()
    {
        $tasks = Task::where('project_id', $this->project->id)->get();

        return view('livewire.kanban-board', [
            'tasksToDo'         => $tasks->where('status', 'To Do'),
            'tasksInProgress'   => $tasks->where('status', 'In Progress'),
            'tasksReview'       => $tasks->where('status', 'Review'),
            'tasksRevision'     => $tasks->where('status', 'Revision'),
            'tasksDone'         => $tasks->where('status', 'Done'),
        ])->layout('components.layouts.app');
    }
}