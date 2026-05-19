<div class="h-screen flex flex-col bg-[#F4F7F6] relative overflow-hidden font-sans select-none" 
     x-data="{ showCreateModal: @entangle('showCreateModal'), showTaskModal: @entangle('showModal') }">
    
    <style>
        /* Desain Kotak Bayangan Saat Kartu Diseret (Dropzone Hint) */
        .kanban-ghost-placeholder {
            background: rgba(79, 70, 229, 0.04) !important;
            border: 2px dashed #4f46e5 !important;
            border-radius: 1.25rem !important;
            height: 110px !important;
            position: relative !important;
            opacity: 0.8 !important;
            margin-bottom: 1rem !important;
        }
        /* Tulisan Indikator Di Dalam Kotak Putus-Putus */
        .kanban-ghost-placeholder::after {
            content: "📍 LETAKKAN DI SINI";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 10px;
            font-weight: 800;
            color: #4f46e5;
            letter-spacing: 0.1em;
        }
        /* Efek Transparansi Kartu Asal Saat Sedang Dipegang Kursor */
        .kanban-chosen-card {
            opacity: 0.3 !important;
        }
        /* Efek Melayang Kartu Menempel Di Ujung Kursor Pengguna */
        .kanban-drag-card {
            transform: scale(1.04) rotate(1.5deg) !important;
            box-shadow: 0 30px 60px rgba(0,0,0,0.15) !important;
            background: #ffffff !important;
            border-color: #4f46e5 !important;
        }
    </style>

    <div class="absolute top-0 left-0 w-full h-[450px] bg-gradient-to-b from-slate-200/50 via-gray-100/20 to-transparent -z-10 pointer-events-none transform-gpu"></div>
    <div class="absolute top-[-15%] right-[-8%] w-[650px] h-[650px] rounded-full bg-gradient-to-br from-indigo-100/30 via-slate-100/10 to-transparent blur-3xl -z-10 pointer-events-none transform-gpu"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-gradient-to-tr from-slate-200/20 to-transparent blur-3xl -z-10 pointer-events-none transform-gpu"></div>

    <x-navbar title="Scrumboard Workspace: {{ $project->name }}" />

    <div class="px-6 sm:px-8 py-4 flex flex-wrap justify-between items-center bg-white/40 backdrop-blur-2xl border-b border-gray-200/60 shadow-[0_2px_12px_rgba(0,0,0,0.01)] z-30 relative gap-4 transition-all duration-300">
        
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-72 group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       wire:model.live="searchQuery" 
                       placeholder="Cari rincian tugas tim..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-white/90 backdrop-blur-md border border-gray-200/80 rounded-2xl text-xs font-bold text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all outline-none shadow-inner">
            </div>
            
            <div class="relative">
                <select wire:model.live="filterPriority" 
                        class="appearance-none bg-white/90 backdrop-blur-md border border-gray-200/80 text-gray-600 text-xs font-extrabold py-2.5 pl-4 pr-10 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all outline-none shadow-sm cursor-pointer">
                    <option value="">Semua Tingkat Prioritas</option>
                    <option value="High">High 🔴</option>
                    <option value="Medium">Medium 🟡</option>
                    <option value="Low">Low 🟢</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>
        
        <button type="button" 
                @click="showCreateModal = true" 
                class="relative px-6 py-3 bg-gray-900 text-white rounded-2xl font-black text-xs tracking-wider uppercase shadow-[0_8px_24px_rgba(0,0,0,0.12)] hover:shadow-[0_12px_28px_rgba(0,0,0,0.22)] hover:bg-black hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 group overflow-hidden flex items-center gap-2 cursor-pointer w-full sm:w-auto justify-center">
            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.8s_infinite]"></div>
            <span class="text-base font-normal leading-none mt-[-2px]">+</span>
            <span>Tambah Kerja Baru</span>
        </button>
    </div>

    <div class="flex-1 overflow-x-auto overflow-y-hidden p-6 sm:p-8 relative z-10 custom-horizontal-track" 
         style="scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent;">
        <div class="flex gap-6 h-full min-w-max items-start pb-4">
            
            @foreach([
                'To Do' => ['tasks' => $tasksToDo, 'bg' => 'bg-slate-50', 'border' => 'border-slate-200/80', 'header' => 'bg-white/70', 'dot' => 'bg-slate-400', 'text' => 'text-slate-700'],
                'In Progress' => ['tasks' => $tasksInProgress, 'bg' => 'bg-blue-50/40', 'border' => 'border-blue-100/70', 'header' => 'bg-blue-50/50', 'dot' => 'bg-blue-500', 'text' => 'text-blue-700'],
                'Review' => ['tasks' => $tasksReview, 'bg' => 'bg-amber-50/40', 'border' => 'border-amber-100/70', 'header' => 'bg-amber-50/50', 'dot' => 'bg-amber-500', 'text' => 'text-amber-700'],
                'Done' => ['tasks' => $tasksDone, 'bg' => 'bg-emerald-50/40', 'border' => 'border-emerald-100/70', 'header' => 'bg-emerald-50/50', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-700']
            ] as $status => $meta)
            
            <div wire:key="col-frame-{{ Str::slug($status) }}" 
                 class="w-[335px] h-full flex flex-col bg-white/50 backdrop-blur-xl rounded-[2.2rem] border {{ $meta['border'] }} shadow-[0_12px_34px_rgba(0,0,0,0.02)] shrink-0 overflow-hidden transition-all duration-300 hover:shadow-[0_12px_34px_rgba(0,0,0,0.05)] hover:bg-white/60">
                
                <div class="px-6 py-5 border-b {{ $meta['border'] }} {{ $meta['header'] }} flex justify-between items-center backdrop-blur-sm z-10">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $meta['dot'] }} shadow-[0_0_12px_rgba(0,0,0,0.15)]"></span>
                        <h3 class="font-black text-gray-900 text-xs tracking-wider uppercase">{{ $status }}</h3>
                    </div>
                    <span class="{{ $meta['bg'] }} {{ $meta['text'] }} text-[10px] font-black px-3 py-1 rounded-xl border {{ $meta['border'] }} shadow-sm">
                        {{ $meta['tasks'] ? $meta['tasks']->count() : 0 }} CARD
                    </span>
                </div>

                <div wire:ignore 
                     class="flex-1 overflow-y-auto sortable-list p-5 space-y-4 rounded-b-[2.2rem] min-h-[450px] bg-transparent/5" 
                     data-status="{{ $status }}" 
                     style="scrollbar-width: none;">
                    
                    @if($meta['tasks'] && $meta['tasks']->isNotEmpty())
                        @foreach($meta['tasks'] as $task)
                            <div wire:key="task-card-element-{{ $task->id }}" 
                                 wire:click="openTaskDetail({{ $task->id }})" 
                                 class="bg-white p-5 rounded-2xl border border-gray-200/60 shadow-[0_4px_16px_rgba(0,0,0,0.01)] cursor-grab active:cursor-grabbing hover:border-indigo-400 hover:shadow-[0_16px_36px_rgba(79,70,229,0.06)] hover:-translate-y-1 transition-all duration-300 group relative transform-gpu" 
                                 data-id="{{ $task->id }}">
                                
                                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-gray-50 to-transparent rounded-bl-full opacity-0 group-hover:opacity-100 transition-all duration-500 -z-10"></div>

                                <div class="flex justify-between items-center mb-3.5">
                                    @if(isset($task->priority))
                                        @php
                                            $prioStyle = match(strtolower($task->priority)) {
                                                'high' => 'bg-red-50 text-red-600 border-red-100/60',
                                                'medium' => 'bg-amber-50 text-amber-600 border-amber-100/60',
                                                default => 'bg-slate-50 text-slate-500 border-slate-200/60'
                                            };
                                        @endphp
                                        <span class="text-[9px] font-black tracking-widest px-2.5 py-1 rounded-lg uppercase border {{ $prioStyle }} shadow-sm">
                                            {{ $task->priority }}
                                        </span>
                                    @endif

                                    <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 border border-white shadow-sm text-gray-700 flex items-center justify-center font-black text-[10px] group-hover:scale-105 group-hover:border-indigo-200 transition-all" title="{{ $task->assignee->name ?? 'Unassigned' }}">
                                        {{ substr($task->assignee->name ?? '?', 0, 1) }}
                                    </div>
                                </div>
                                
                                <h4 class="font-extrabold text-gray-900 text-xs leading-relaxed group-hover:text-indigo-900 line-clamp-2 mb-4 transition-colors">
                                    {{ $task->title }}
                                </h4>

                                <div class="flex justify-between items-center pt-3.5 border-t border-gray-100 text-[10px] font-bold">
                                    @if($task->status === 'Review')
                                        <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg shadow-xs bg-amber-50 text-amber-600 border border-amber-100/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> WAITING PM
                                        </span>
                                    @elseif($task->status === 'Revision')
                                        <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg shadow-xs bg-rose-50 text-rose-600 border-rose-100/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> REVISI PM
                                        </span>
                                    @else
                                        <span class="text-gray-400 flex items-center gap-1.5 bg-gray-50/80 border border-gray-200/40 px-2 py-1 rounded-lg">
                                            <svg class="w-3 h-3 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $task->created_at ? $task->created_at->format('d M') : now()->format('d M') }}
                                        </span>
                                    @endif

                                    @if($task->proofs && $task->proofs->count() > 0)
                                        <span class="text-indigo-600 flex items-center gap-1 bg-indigo-50/50 px-2 py-1 rounded-lg border border-indigo-100/50 shadow-xs">
                                            <svg class="w-3 h-3 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 0l-3.536 3.536m3.536-3.536L18.364 12m-3.536-3.536l-3.536-3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $task->proofs->count() }} BUKTI
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="py-12 border border-dashed border-gray-200 rounded-2xl text-center flex flex-col items-center justify-center pointer-events-none empty-placeholder">
                            <span class="text-xl mb-1 filter grayscale opacity-40">📥</span>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kolom Kosong</p>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach

        </div>
    </div>

    <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4">
        <div x-show="showCreateModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showCreateModal = false"></div>
        
        <div x-show="showCreateModal" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="relative transform overflow-hidden rounded-[2.5rem] bg-white p-8 shadow-[0_30px_70px_rgba(0,0,0,0.28)] w-full max-w-lg border border-gray-100 z-[101]">
            
            <div class="flex justify-between items-start pb-5 border-b border-gray-100 mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">Inisiasi Komponen Kerja</h3>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-1">Workspace Manajemen Operasional</p>
                </div>
                <button type="button" @click="showCreateModal = false" class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 text-gray-400 hover:text-rose-600 flex items-center justify-center text-lg font-bold transition-colors cursor-pointer shadow-xs">&times;</button>
            </div>

            <form wire:submit.prevent="createTask" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Judul Pekerjaan</label>
                    <input type="text" wire:model="newTaskTitle" required placeholder="Contoh: Pembuatan Landing Page Slicing Tailwind" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-xs font-bold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 outline-none transition-all shadow-inner">
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Skala Prioritas</label>
                        <select wire:model="newTaskPriority" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-xs font-extrabold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 outline-none transition-all cursor-pointer shadow-sm">
                            <option value="Low">Low 🟢</option>
                            <option value="Medium">Medium 🟡</option>
                            <option value="High">High 🔴</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Delegasikan Ke</label>
                        <select wire:model="newTaskAssignee" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-xs font-extrabold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 outline-none transition-all cursor-pointer shadow-sm">
                            <option value="">-- Pilih Anggota Tim --</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Spesifikasi Keterangan Detail</label>
                    <textarea wire:model="newTaskDescription" rows="4" placeholder="Jelaskan instruksi arsitektur sistem, struktur folder, atau rincian detail tugas lainnya..." class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-xs font-bold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 outline-none transition-all shadow-inner"></textarea>
                </div>

                <button type="submit" wire:loading.attr="disabled" class="w-full bg-gray-900 hover:bg-black text-white rounded-2xl py-4 font-black text-xs uppercase tracking-wider transition-all shadow-md hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 cursor-pointer mt-2">
                    <span wire:loading.remove>Simpan & Daftarkan Progres</span>
                    <span wire:loading>Memproses Transmisi...</span>
                </button>
            </form>
        </div>
    </div>

    <div x-cloak x-show="showTaskModal" class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
        <div x-show="showTaskModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" @click="showTaskModal = false"></div>

        <div x-show="showTaskModal" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-[0_30px_70px_rgba(0,0,0,0.3)] transition-all sm:w-full sm:max-w-5xl border border-gray-100 z-[101]" x-trap="showTaskModal">
            
            @if($selectedTask)
            <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-3 mb-2.5">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 border border-gray-200 bg-white px-2.5 py-1 rounded-xl shadow-xs">ID WORK: #{{ $selectedTask->id }}</span>
                        <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-xl border shadow-xs {{ $selectedTask->status === 'Done' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100' }}">
                            {{ $selectedTask->status }}
                        </span>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight leading-tight">{{ $selectedTask->title }}</h3>
                </div>
                <button type="button" @click="showTaskModal = false" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100 shadow-xs transition-colors cursor-pointer font-bold text-lg">&times;</button>
            </div>

            <div class="flex flex-col lg:flex-row h-full max-h-[70vh]">
                
                <div class="lg:w-3/5 px-8 py-6 overflow-y-auto border-r border-gray-100 custom-vertical-scrollbar" style="scrollbar-width: thin;">
                    <div class="grid grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-3xl border border-gray-200/60 text-[11px] font-bold text-gray-500">
                        <div>PEKERJA TIM: <span class="text-gray-900 font-black block mt-0.5 text-xs">{{ $selectedTask->assignee->name ?? 'Belum Ditugaskan' }}</span></div>
                        <div>URGENCY PRIORITY: <span class="text-indigo-600 font-black block mt-0.5 text-xs uppercase">{{ $selectedTask->priority ?? 'Normal' }}</span></div>
                    </div>

                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2.5">Spesifikasi Lembar Kerja</h4>
                    <p class="text-xs font-bold text-gray-700 leading-relaxed bg-white border border-gray-100 p-5 rounded-3xl shadow-xs whitespace-pre-wrap mb-6">{{ $selectedTask->description ?? 'Tidak disertakan keterangan pendukung.' }}</p>

                    @if($selectedTask->status === 'Review')
                        @role('Founder|Co-Founder|HR')
                        <div class="mb-6 p-5 bg-amber-50/60 border border-amber-200 rounded-3xl shadow-xs">
                            <h4 class="text-xs font-black text-amber-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>🛡️</span> Panel Otoritas Peninjau Kualitas (Quality Assurance / PM)
                            </h4>
                            <p class="text-[11px] font-bold text-amber-700 leading-relaxed mb-4">
                                Developer telah melampirkan berkas penyerahan. Silakan periksa kesesuaian screenshot UI dan repositori Git sebelum mengambil tindakan keputusan di bawah:
                            </p>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" 
                                        wire:click="updateTaskStatus({{ $selectedTask->id }}, 'Done')" 
                                        class="py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm cursor-pointer text-center">
                                    ✓ Setujui (Selesai)
                                </button>
                                <button type="button" 
                                        wire:click="updateTaskStatus({{ $selectedTask->id }}, 'Revision')" 
                                        class="py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm cursor-pointer text-center">
                                    ✕ Tolak & Minta Revisi
                                </button>
                            </div>
                        </div>
                        @endrole
                    @endif

                    @if(in_array($selectedTask->status, ['To Do', 'In Progress', 'Revision']))
                    <div class="bg-indigo-50/40 border border-indigo-100 p-6 rounded-3xl shadow-xs relative overflow-hidden mb-6">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-bl-full -z-10"></div>
                        <h4 class="text-xs font-black text-indigo-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Zona Serah Terima Fitur (Wajib Developer)
                        </h4>
                        
                        <form wire:submit.prevent="submitProof" class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-indigo-700 uppercase tracking-widest mb-1.5">1. Screenshot Hasil Pembuatan UI (.jpg/.png)</label>
                                <input type="file" wire:model="uiScreenshot" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-indigo-600 file:text-white bg-white border border-indigo-100 rounded-xl cursor-pointer shadow-xs"/>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-indigo-700 uppercase tracking-widest mb-1.5">2. Screenshot Bukti Push Git Repository (.jpg/.png)</label>
                                <input type="file" wire:model="repoPush" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-gray-900 file:text-white bg-white border border-indigo-100 rounded-xl cursor-pointer shadow-xs"/>
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-xs font-black uppercase tracking-wider shadow-md transition-colors cursor-pointer">
                                Unggah Berkas & Ajukan Otorisasi PM &rarr;
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($selectedTask->proofs && $selectedTask->proofs->isNotEmpty())
                    <div class="space-y-4 pt-2">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">📁 Berkas Lampiran Hasil Transmisi</h4>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($selectedTask->proofs as $proof)
                                <div class="rounded-2xl overflow-hidden border border-gray-200 bg-black relative group shadow-xs">
                                    <span class="absolute top-2 left-2 bg-black/70 backdrop-blur-md text-[8px] font-black text-white px-2 py-0.5 rounded-md z-10 tracking-wider">UI RESULT</span>
                                    <img src="{{ asset('storage/' . $proof->ui_screenshot_path) }}" class="w-full h-28 object-cover opacity-90 group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="rounded-2xl overflow-hidden border border-gray-200 bg-black relative group shadow-xs">
                                    <span class="absolute top-2 left-2 bg-black/70 backdrop-blur-md text-[8px] font-black text-white px-2 py-0.5 rounded-md z-10 tracking-wider">GIT REPO PUSH</span>
                                    <img src="{{ asset('storage/' . $proof->repo_push_path) }}" class="w-full h-28 object-cover opacity-90 group-hover:scale-105 transition-transform duration-300">
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div class="lg:w-2/5 flex flex-col bg-gray-50/50">
                    <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex justify-between items-center">
                        <h4 class="font-black text-gray-900 text-xs tracking-wider uppercase">Diskusi & Lembar Catatan Kerja</h4>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    
                    <div class="flex-1 p-6 overflow-y-auto space-y-4 custom-vertical-scrollbar" style="scrollbar-width: thin;">
                        <div class="text-center text-[9px] font-black text-gray-300 uppercase tracking-widest my-2">Awal Enkripsi Alur Diskusi</div>
                        
                        <div class="flex gap-3">
                            <div class="w-7 h-7 rounded-lg bg-gray-900 text-white shrink-0 flex items-center justify-center font-black text-[10px] shadow-xs">X</div>
                            <div class="bg-white border border-gray-200/60 p-3 rounded-2xl rounded-tl-sm shadow-xs max-w-[85%]">
                                <p class="text-[9px] font-black text-gray-400 mb-0.5">System Bot Workspace</p>
                                <p class="text-[11px] font-bold text-gray-600 leading-relaxed">Kompatibilitas obrolan terhubung aman. Silakan gunakan ruang ini untuk mencatat instruksi perbaikan atau kendala arsitektur database proyek.</p>
                            </div>
                        </div>

                        @if(isset($discussionFeed) && count($discussionFeed) > 0)
                            @foreach($discussionFeed as $chat)
                                @php 
                                    $isMe = $chat['sender_id'] === Auth::id(); 
                                @endphp
                                <div class="flex gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                    <div class="w-7 h-7 rounded-lg text-white flex items-center justify-center font-black text-[10px] shrink-0 shadow-xs {{ $isMe ? 'bg-indigo-600' : 'bg-gray-800' }}">
                                        {{ substr($chat['sender_name'], 0, 1) }}
                                    </div>
                                    <div class="p-3.5 rounded-2xl shadow-xs max-w-[80%] border {{ $isMe ? 'bg-gray-900 text-white border-black rounded-tr-sm' : 'bg-white text-gray-800 border-gray-200/80 rounded-tl-sm' }}">
                                        <div class="flex justify-between items-center gap-4 mb-1">
                                            <p class="text-[9px] font-black uppercase tracking-wider {{ $isMe ? 'text-indigo-300/80' : 'text-indigo-600/80' }}">{{ $chat['sender_name'] }}</p>
                                        </div>
                                        <p class="text-xs font-bold leading-relaxed whitespace-pre-wrap {{ $isMe ? 'text-gray-100' : 'text-gray-700' }}">{{ $chat['message'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="p-4 bg-white border-t border-gray-100">
                        <form wire:submit.prevent="sendComment" class="relative">
                            <input type="text" 
                                   wire:model="newComment" 
                                   placeholder="Ketik pesan diskusi internal tim..." 
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl py-3.5 pl-4 pr-12 text-xs font-bold text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5 outline-none transition-all shadow-inner">
                            <button type="submit" 
                                    class="absolute right-2 top-2 bg-gray-900 hover:bg-black text-white w-9 h-9 rounded-xl flex items-center justify-center transition-colors cursor-pointer shadow-sm">
                                <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => { initKanbanSortable(); });
        document.addEventListener('livewire:navigated', () => { initKanbanSortable(); });
        
        // Mencegah matinya fungsi seret pasca dom-morphing sirkulasi pembaruan data server
        Livewire.hook('morph.updated', ({ el, component }) => {
            initKanbanSortable();
        });

        function initKanbanSortable() {
            const trackLanes = document.querySelectorAll('.sortable-list');
            
            trackLanes.forEach(lane => {
                // Hancurkan instansi duplikasi lama guna mencegah kebocoran RAM browser
                if(lane.sortableInstance) { 
                    lane.sortableInstance.destroy(); 
                }
                
                // Konfigurasi mekanik drag-and-drop mutakhir
                lane.sortableInstance = new Sortable(lane, {
                    group: 'xgrow_kanban_workspace_group',
                    animation: 260,
                    fallbackOnBody: false,
                    swapThreshold: 0.65,
                    invertSwap: true,
                    emptyInsertThreshold: 20, // Memperlebar jangkauan tangkapan jika kolom kosong melong
                    ghostClass: 'kanban-ghost-placeholder', // 💥 SUNTIKAN UTAMA: MEMANGGIL KOTAK PUTUS-PUTUS DROPMENU
                    chosenClass: 'kanban-chosen-card',
                    dragClass: 'kanban-drag-card',
                    
                    onStart: function(evt) {
                        // Sembunyikan tulisan "Kolom Kosong" bawaan agar tidak mengganggu rendering jalannya kartu
                        const placeholder = evt.to.querySelector('.empty-placeholder');
                        if (placeholder) placeholder.style.display = 'none';
                    },
                    
                    onEnd: function (evt) {
                        const taskId = evt.item.getAttribute('data-id');
                        const newStatus = evt.to.getAttribute('data-status');
                        const oldStatus = evt.from.getAttribute('data-status');
                        
                        if (newStatus === oldStatus) {
                            // Jika dibatalkan atau ditaruh di kolom yang sama, munculkan lagi tulisan kolom kosongnya
                            const placeholder = evt.from.querySelector('.empty-placeholder');
                            if (placeholder && evt.from.children.length <= 1) placeholder.style.display = 'flex';
                            return;
                        }
                        
                        // 💥 SOLUSI UTAMA: Menggunakan @this langsung milik Blade Compiler untuk memicu fungsi update backend secara instan!
                        @this.updateTaskStatus(taskId, newStatus);
                    }
                });
            });
        }
    </script>
</div>