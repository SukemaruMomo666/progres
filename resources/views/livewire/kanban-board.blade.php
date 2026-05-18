<div class="flex flex-col h-screen" x-data="{ showModal: @entangle('showModal') }">
    <!-- Header Workspace -->
    <nav class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center shrink-0 shadow-sm">
        <div>
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-gray-400 hover:text-black mb-1 flex items-center gap-1 transition">
                &larr; Kembali ke Dashboard
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $project->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm font-semibold bg-gray-100 px-3 py-1.5 rounded-lg text-gray-600">PIC: {{ $project->pic->name }}</span>
        </div>
    </nav>

    <!-- Board Area (Scrollable Horizontal) -->
    <div class="flex-1 overflow-x-auto bg-[#F9FAFB] p-8">
        <div class="flex gap-6 h-full min-w-max">
            
            @foreach([
                'To Do' => ['tasks' => $tasksToDo, 'bg' => 'bg-gray-100', 'text' => 'text-gray-700'],
                'In Progress' => ['tasks' => $tasksInProgress, 'bg' => 'bg-blue-50/50', 'text' => 'text-blue-700'],
                'Revision' => ['tasks' => $tasksRevision, 'bg' => 'bg-red-50/50', 'text' => 'text-red-700'],
                'Review' => ['tasks' => $tasksReview, 'bg' => 'bg-amber-50/50', 'text' => 'text-amber-700'],
                'Done' => ['tasks' => $tasksDone, 'bg' => 'bg-emerald-50/50', 'text' => 'text-emerald-700']
            ] as $status => $meta)
            
            <!-- Column -->
            <div class="w-80 flex flex-col bg-white rounded-2xl p-4 border border-gray-200 shrink-0 shadow-[0_4px_12px_rgba(0,0,0,0.01)]">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full @if($status == 'To Do') bg-gray-400 @elseif($status == 'In Progress') bg-blue-500 @elseif($status == 'Revision') bg-red-500 @elseif($status == 'Review') bg-amber-500 @else bg-emerald-500 @endif"></span>
                        <h3 class="font-bold text-gray-800 text-sm tracking-tight">{{ $status }}</h3>
                    </div>
                    <span class="{{ $meta['bg'] }} {{ $meta['text'] }} text-xs font-bold px-2 py-0.5 rounded-full">{{ $meta['tasks']->count() }}</span>
                </div>

                <!-- Sortable Card Container -->
                <div class="flex-1 overflow-y-auto sortable-list space-y-3 pb-4 min-h-[150px]" data-status="{{ $status }}">
                    @foreach($meta['tasks'] as $task)
                        <div wire:click="openTaskDetail({{ $task->id }})" 
                             class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm cursor-pointer hover:border-black hover:shadow-md transition duration-200 active:cursor-grabbing group relative" 
                             data-id="{{ $task->id }}">
                            
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] font-bold tracking-wider px-2 py-0.5 rounded bg-gray-100 text-gray-600 uppercase">
                                    {{ $task->priority }}
                                </span>
                            </div>
                            
                            <h4 class="font-semibold text-gray-900 text-sm leading-snug group-hover:text-black transition">
                                {{ $task->title }}
                            </h4>

                            <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-50 text-[11px] text-gray-400">
                                <span class="flex items-center gap-1">
                                    @if($task->status === 'Review') 
                                        <span class="text-amber-500 font-medium">🟡 Waiting QA</span>
                                    @elseif($task->status === 'Revision')
                                        <span class="text-red-500 font-medium">🔴 Needs Fix</span>
                                    @endif
                                </span>
                                <div class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center font-bold text-[9px]">
                                    {{ substr($task->assigned_to ? $task->assignee->name : '?', 0, 1) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach

        </div>
    </div>

    <!-- MODAL DETAIL TASK (Figma Style) -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="$set('showModal', false)"></div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100" x-trap="showModal">
                
                @if($selectedTask)
                <div class="px-6 py-6 border-b border-gray-100 flex justify-between items-start">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Task Detail</span>
                        <h3 class="text-xl font-bold text-gray-900 mt-0.5">{{ $selectedTask->title }}</h3>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-black font-semibold text-lg">&times;</button>
                </div>

                <div class="px-6 py-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <!-- Deskripsi -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deskripsi Kerja</h4>
                        <p class="text-sm text-gray-600 leading-relaxed bg-gray-50 p-4 rounded-xl border border-gray-100">
                            {{ $selectedTask->description ?? 'Tidak ada deskripsi spesifik.' }}
                        </p>
                    </div>

                    <!-- LOGIKA UPLOAD BUKTI (Hanya muncul jika status belum Review/Done, atau sedang Revision) -->
                    @if(in_array($selectedTask->status, ['To Do', 'In Progress', 'Revision']))
                    <form wire:submit.prevent="submitProof" class="space-y-4 pt-4 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-900 mb-1">Form Submit Bukti Kerja (Wajib Dev)</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-2">Screenshot Hasil UI (.jpg/.png)</label>
                                <input type="file" wire:model="uiScreenshot" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800 transition"/>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-2">Screenshot Push Git Repo (.jpg/.png)</label>
                                <input type="file" wire:model="repoPush" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800 transition"/>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Catatan Dev (Optional)</label>
                            <textarea wire:model="devNotes" rows="2" class="block w-full rounded-lg border border-gray-200 p-2.5 text-sm focus:border-black focus:ring-black" placeholder="Tulis catatan pengerjaan atau kendala..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-black text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-gray-800 transition shadow-sm">
                            Submit Bukti & Ajukan Review
                        </button>
                    </form>
                    @endif

                    <!-- RIWAYAT BUKTI & REVISI (Jika sudah pernah upload atau di-reject) -->
                    @if($selectedTask->proofs->isNotEmpty())
                    <div class="pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Bukti Kerja Terlampir</h4>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($selectedTask->proofs as $proof)
                                <div class="border border-gray-100 rounded-xl p-2 bg-gray-50">
                                    <span class="text-[10px] font-bold text-gray-400 block mb-1">UI SCREENSHOT</span>
                                    <img src="{{ asset('storage/' . $proof->ui_screenshot_path) }}" class="rounded-lg max-h-40 w-full object-cover border border-gray-200">
                                </div>
                                <div class="border border-gray-100 rounded-xl p-2 bg-gray-50">
                                    <span class="text-[10px] font-bold text-gray-400 block mb-1">REPO PUSH PROOF</span>
                                    <img src="{{ asset('storage/' . $proof->repo_push_path) }}" class="rounded-lg max-h-40 w-full object-cover border border-gray-200">
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Script SortableJS Core Engine -->
    <script>
        document.addEventListener('livewire:navigated', () => { initSortable(); });
        document.addEventListener('DOMContentLoaded', () => { initSortable(); });

        function initSortable() {
            const lists = document.querySelectorAll('.sortable-list');
            lists.forEach(list => {
                new Sortable(list, {
                    group: 'kanban',
                    animation: 180,
                    ghostClass: 'bg-gray-50',
                    dragClass: 'shadow-2xl',
                    onEnd: function (evt) {
                        const taskId = evt.item.getAttribute('data-id');
                        const newStatus = evt.to.getAttribute('data-status');
                        @this.updateTaskStatus(taskId, newStatus);
                    },
                });
            });
        }
    </script>
</div>