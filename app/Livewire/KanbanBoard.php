<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskProof;
use App\Models\TaskRevision;
use App\Models\ActivityLog;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
use App\Events\KanbanUpdated;
=======
use Illuminate\Support\Facades\DB;
>>>>>>> 29dd71d0627815c589e10a32cfaa69ada1371990

class KanbanBoard extends Component
{
    use WithFileUploads;

    public Project $project;
    public $tasksToDo, $tasksInProgress, $tasksReview, $tasksRevision, $tasksDone;

    public $searchQuery = '';
    public $filterPriority = '';

    public $newTaskTitle, $newTaskPriority = 'Medium', $newTaskAssignee, $newTaskDescription;
    public $newComment = ''; 
    public $discussionFeed = []; 

    public $uiScreenshot, $repoPush;
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
        $query = Task::with(['assignee', 'proofs'])
            ->where('project_id', $this->project->id);

        if (!empty($this->searchQuery)) {
            $query->where('title', 'like', '%' . $this->searchQuery . '%');
        }
        if (!empty($this->filterPriority)) {
            $query->where('priority', $this->filterPriority);
        }

        $tasks = $query->get();
        
        $this->tasksToDo = $tasks->where('status', 'To Do');
        $this->tasksInProgress = $tasks->where('status', 'In Progress');
        $this->tasksReview = $tasks->where('status', 'Review');
        $this->tasksRevision = $tasks->where('status', 'Revision'); 
        $this->tasksDone = $tasks->where('status', 'Done');
    }

    public function loadDiscussion()
    {
        if (!$this->selectedTask) return;

        $proofs = TaskProof::where('task_id', $this->selectedTask->id)
            ->whereNotNull('dev_notes')
            ->get()->map(function($item) {
                return [
                    'sender_name' => $item->developer->name ?? 'Developer',
                    'sender_id'   => $item->submitted_by, // FIXED: Sender ID harus ada untuk Blade
                    'message'     => $item->dev_notes,
                    'created_at'  => $item->created_at,
                ];
            });

        $revisions = TaskRevision::where('task_id', $this->selectedTask->id)
            ->get()->map(function($item) {
                return [
                    'sender_name' => $item->reviewer->name ?? 'PM',
                    'sender_id'   => $item->rejected_by, // FIXED: Sender ID harus ada untuk Blade
                    'message'     => $item->reason,      // Menggunakan 'reason' sesuai database
                    'created_at'  => $item->created_at,
                ];
            });

        $this->discussionFeed = $proofs->concat($revisions)->sortBy('created_at')->toArray();
    }

    public function openTaskDetail($taskId)
    {
        $this->selectedTask = Task::with(['assignee', 'proofs'])->find($taskId);
        $this->loadDiscussion(); 
        $this->showModal = true;
    }

<<<<<<< HEAD
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
            
            broadcast(new KanbanUpdated())->toOthers();
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
        
        broadcast(new KanbanUpdated())->toOthers();
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
        
        broadcast(new KanbanUpdated())->toOthers();
    }

=======
>>>>>>> 29dd71d0627815c589e10a32cfaa69ada1371990
    public function createTask()
    {
        $this->validate(['newTaskTitle' => 'required|string|max:255']);

        DB::transaction(function () {
            $task = Task::create([
                'project_id'  => $this->project->id,
                'title'       => $this->newTaskTitle,
                'description' => $this->newTaskDescription,
                'priority'    => $this->newTaskPriority,
                'assigned_to' => $this->newTaskAssignee ?: null,
                'status'      => 'To Do',
            ]);
            ActivityLog::record('Tambah Tugas', "Membuat tugas baru: '{$task->title}' pada proyek '{$this->project->name}'.");
        });

        $this->reset(['newTaskTitle', 'newTaskDescription', 'newTaskAssignee']);
        $this->loadTasks();
        $this->showCreateModal = false; 
        
        broadcast(new KanbanUpdated())->toOthers();
    }

    public function updateTaskStatus($taskId, $newStatus)
    {
        $task = Task::find($taskId);
        if ($task) {
            $oldStatus = $task->status;
            $task->update(['status' => $newStatus]);
            ActivityLog::record('Update Status', "Tugas '{$task->title}' dipindah dari {$oldStatus} ke {$newStatus}.");
            $this->loadTasks();
            if ($this->selectedTask && $this->selectedTask->id == $taskId) $this->selectedTask = $task;
        }
    }

<<<<<<< HEAD
            if ($this->selectedTask && $this->selectedTask->id == $taskId) {
                $this->selectedTask = Task::with(['assignee', 'proofs'])->find($taskId);
            }
            
            broadcast(new KanbanUpdated())->toOthers();
        }
    }

    #[On('echo:workspace-channel,KanbanUpdated')]
    public function refreshBoard()
    {
        $this->loadTasks();
        
        // Memuat ulang diskusi jika sedang ada modal terbuka yang relevan
        if ($this->selectedTask) {
            $this->selectedTask = Task::with(['assignee', 'proofs'])->find($this->selectedTask->id);
            $this->loadDiscussion();
=======
    public function submitProof()
    {
        $this->validate(['uiScreenshot' => 'required|image|max:2048', 'repoPush' => 'required|image|max:2048']);

        DB::transaction(function () {
            $uiPath = $this->uiScreenshot->store('proofs/ui', 'public');
            $repoPath = $this->repoPush->store('proofs/repo', 'public');

            TaskProof::create([
                'task_id'            => $this->selectedTask->id,
                'submitted_by'       => Auth::id(),
                'ui_screenshot_path' => $uiPath,
                'repo_push_path'     => $repoPath,
                'dev_notes'          => 'Mengirim bukti pengerjaan.'
            ]);

            $this->selectedTask->update(['status' => 'Review']);
            ActivityLog::record('Serah Terima Tugas', "Developer menyerahkan bukti pengerjaan untuk tugas '{$this->selectedTask->title}'.");
        });

        $this->loadTasks();
        $this->selectedTask = Task::with(['assignee', 'proofs'])->find($this->selectedTask->id);
        $this->loadDiscussion();
        $this->reset(['uiScreenshot', 'repoPush']);
    }

    public function sendComment()
    {
        $this->validate(['newComment' => 'required|string|max:500']);

        DB::transaction(function () {
            TaskRevision::create([
                'task_id'     => $this->selectedTask->id,
                'rejected_by' => Auth::id(),
                'reason'      => $this->newComment // Sesuai DB Dump
            ]);
            ActivityLog::record('Diskusi Revisi', "Menambah catatan revisi pada tugas '{$this->selectedTask->title}'.");
        });

        $this->newComment = '';
        $this->loadDiscussion(); 
    }

    public function deleteTask($taskId)
    {
        $task = Task::find($taskId);
        if ($task) {
            $taskTitle = $task->title;
            $task->delete();
            ActivityLog::record('Hapus Tugas', "Tugas '{$taskTitle}' telah dihapus dari sistem.");
            $this->showModal = false;
            $this->loadTasks();
>>>>>>> 29dd71d0627815c589e10a32cfaa69ada1371990
        }
    }

    public function render()
    {
        return view('livewire.kanban-board')->layout('components.layouts.app');
    }
}