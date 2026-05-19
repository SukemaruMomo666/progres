<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskProof;
use App\Models\TaskRevision;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class KanbanBoard extends Component
{
    use WithFileUploads;

    public Project $project;
    
    // Penampung Data 4 Kolom Utama Papan Board
    public $tasksToDo, $tasksInProgress, $tasksReview, $tasksDone;

    // Parameter Filter Konten
    public $searchQuery = '';
    public $filterPriority = '';

    // Variabel Form Input Data Task Baru & Diskusi
    public $newTaskTitle, $newTaskPriority = 'Medium', $newTaskAssignee, $newTaskDescription;
    public $newComment = ''; 
    public $discussionFeed = []; 

    // Properti Unggah Berkas Digital (Wajib untuk Livewire File Uploads)
    public $uiScreenshot;
    public $repoPush;

    // State Modal Toggle
    public $showModal = false;
    public $showCreateModal = false;
    public $selectedTask = null;

    public function updatedSearchQuery() { $this->loadTasks(); }
    public function updatedFilterPriority() { $this->loadTasks(); }

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->loadTasks();
    }

    public function loadTasks()
    {
        // Tarik data tugas tim berdasarkan project id aktif
        $query = Task::with(['assignee', 'proofs'])->where('project_id', $this->project->id);

        if (!empty($this->searchQuery)) {
            $query->where('title', 'like', '%' . $this->searchQuery . '%');
        }
        if (!empty($this->filterPriority)) {
            $query->where('priority', $this->filterPriority);
        }

        $tasks = $query->get();
        
        // Memisahkan data tugas ke dalam 4 kolom mandatori
        $this->tasksToDo = $tasks->where('status', 'To Do');
        $this->tasksInProgress = $tasks->where('status', 'In Progress');
        $this->tasksReview = $tasks->where('status', 'Review');
        $this->tasksDone = $tasks->where('status', 'Done');
    }

    /**
     * Sinkronisasi Data Transmisi Kerja & Revisi Menjadi Feed Obrolan
     */
    public function loadDiscussion()
    {
        if (!$this->selectedTask) return;

        // 1. Ambil catatan penyerahan tugas dari Developer
        $proofs = TaskProof::where('task_id', $this->selectedTask->id)
            ->whereNotNull('dev_notes')
            ->get()
            ->map(function($item) {
                $user = User::find($item->submitted_by);
                return [
                    'sender_name' => $user->name ?? 'Developer',
                    'sender_id' => $item->submitted_by,
                    'message' => $item->dev_notes,
                    'created_at' => $item->created_at,
                ];
            })->toArray();

        // 2. Ambil catatan instruksi perbaikan dari Project Manager
        $revisions = TaskRevision::where('task_id', $this->selectedTask->id)
            ->get()
            ->map(function($item) {
                $user = User::find($item->rejected_by);
                return [
                    'sender_name' => $user->name ?? 'Project Manager',
                    'sender_id' => $item->rejected_by,
                    'message' => $item->reason,
                    'created_at' => $item->created_at,
                ];
            })->toArray();

        // 3. Satukan alur data secara kronologis waktu nyata
        $merged = array_merge($proofs, $revisions);
        usort($merged, function($a, $b) {
            return strtotime($a['created_at']) <=> strtotime($b['created_at']);
        });

        $this->discussionFeed = $merged;
    }

    public function openTaskDetail($taskId)
    {
        $this->selectedTask = Task::with(['assignee', 'proofs'])->find($taskId);
        $this->loadDiscussion(); 
        $this->showModal = true;
    }

    /**
     * 🌟 FITUR BARU: Menghapus tugas dari sistem beserta akses otoritasnya
     */
    public function deleteTask($taskId)
    {
        $task = Task::find($taskId);
        
        if ($task) {
            $task->delete(); // Akan dipindah ke tong sampah (Soft Delete) karena ada deleted_at
            
            $this->showModal = false;
            $this->selectedTask = null;
            $this->loadTasks();
        }
    }

    public function submitProof()
    {
        $this->validate([
            'uiScreenshot' => 'required|image|max:2048', 
            'repoPush' => 'required|image|max:2048',
        ]);

        $uiPath = $this->uiScreenshot->store('proofs/ui', 'public');
        $repoPath = $this->repoPush->store('proofs/repo', 'public');

        $proof = new TaskProof();
        $proof->task_id = $this->selectedTask->id;
        $proof->submitted_by = Auth::id();
        $proof->ui_screenshot_path = $uiPath;
        $proof->repo_push_path = $repoPath;
        $proof->dev_notes = 'Bukti pengerjaan (Screenshot UI & Hasil Push Git) berhasil diserahkan oleh developer.';
        $proof->save(); 

        $task = Task::find($this->selectedTask->id);
        if ($task) {
            $task->status = 'Review';
            $task->save();
        }

        $this->reset(['uiScreenshot', 'repoPush']);
        $this->loadTasks();
        $this->selectedTask = Task::with(['assignee', 'proofs'])->find($task->id);
        $this->loadDiscussion();
    }

    public function sendComment()
    {
        $this->validate(['newComment' => 'required|string|max:500']);

        $chat = new TaskRevision();
        $chat->task_id = $this->selectedTask->id;
        $chat->rejected_by = Auth::id();
        $chat->reason = $this->newComment;
        $chat->save(); 

        $this->newComment = '';
        $this->loadDiscussion(); 
    }

    public function createTask()
    {
        $this->validate(['newTaskTitle' => 'required|string|max:255']);

        $task = new Task();
        $task->project_id = $this->project->id;
        $task->title = $this->newTaskTitle;
        $task->description = $this->newTaskDescription;
        $task->priority = $this->newTaskPriority;
        $task->assigned_to = $this->newTaskAssignee ?: null;
        $task->status = 'To Do';
        $task->save();

        $this->reset(['newTaskTitle', 'newTaskDescription', 'newTaskAssignee']);
        $this->loadTasks();
        $this->showCreateModal = false; 
    }

    public function updateTaskStatus($taskId, $newStatus)
    {
        $task = Task::find($taskId);
        if ($task) {
            $task->status = $newStatus;
            $task->save(); 
            
            $this->loadTasks();

            if ($this->selectedTask && $this->selectedTask->id == $taskId) {
                $this->selectedTask = Task::with(['assignee', 'proofs'])->find($taskId);
            }
        }
    }

    public function render()
    {
        return view('livewire.kanban-board')->layout('components.layouts.app');
    }
}