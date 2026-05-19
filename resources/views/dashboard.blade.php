<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - XGrow Workspace</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; } 
        /* Custom Scrollbar Premium */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F4F7F6] text-gray-800 antialiased relative overflow-x-hidden" 
      x-data="{ 
          openCreateModal: false, 
          openEditModal: false, 
          editUrl: '', 
          form: { 
              name: '', 
              client_name: '', 
              client_phone: '', 
              pic_id: '', 
              finder_id: '', 
              total_price: '', 
              start_date: '', 
              deadline: '' 
          } 
      }">

    <div class="absolute top-0 left-0 w-full h-[400px] bg-gradient-to-b from-gray-200/60 via-gray-100/30 to-transparent -z-10 pointer-events-none"></div>
    <div class="absolute top-[-10%] right-[-5%] w-[600px] h-[600px] rounded-full bg-gradient-to-br from-indigo-100/40 to-transparent blur-3xl -z-10 pointer-events-none"></div>

    <x-navbar title="Project Dashboard" />

    <main class="max-w-7xl mx-auto px-8 py-12 relative z-10">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
            <div>
                <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Active Projects</h2>
                <p class="text-gray-500 mt-2 font-medium text-sm">Pantau dan kelola seluruh progres operasional studio secara <span class="font-bold text-gray-700">real-time</span>.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.users.index') }}" class="group relative px-5 py-2.5 bg-white/80 backdrop-blur-md rounded-2xl font-extrabold text-sm text-gray-700 shadow-sm border border-gray-200/80 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                    <span class="flex items-center gap-2">👥 Anggota Tim</span>
                </a>

                @role('Founder|Co-Founder|HR')
                    <a href="{{ route('performance.index') }}" class="group relative px-5 py-2.5 bg-white/80 backdrop-blur-md rounded-2xl font-extrabold text-sm text-gray-700 shadow-sm border border-gray-200/80 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                        <span class="flex items-center gap-2">🎯 KPI Tim</span>
                    </a>
                    <a href="{{ route('finance.index') }}" class="group relative px-5 py-2.5 bg-white/80 backdrop-blur-md rounded-2xl font-extrabold text-sm text-gray-700 shadow-sm border border-gray-200/80 hover:border-gray-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                        <span class="flex items-center gap-2">📊 Buku Kas</span>
                    </a>
                    <button type="button" @click="openCreateModal = true" class="relative px-6 py-2.5 bg-gray-900 text-white rounded-2xl font-extrabold text-sm shadow-[0_8px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_12px_25px_rgba(0,0,0,0.25)] hover:-translate-y-0.5 transition-all duration-300 group overflow-hidden cursor-pointer">
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                        <span class="relative z-10 flex items-center gap-2"><span class="text-lg leading-none">+</span> New Project</span>
                    </button>
                @endrole
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($projects as $project)
                <div class="group block bg-white/70 backdrop-blur-xl rounded-[2rem] p-7 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] hover:-translate-y-2 hover:bg-white transition-all duration-400 relative overflow-hidden">
                    
                    <a href="{{ route('project.board', $project->id) }}" class="absolute inset-0 z-0"></a>

                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-gray-100 to-transparent rounded-bl-full opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>

                    <div class="flex justify-between items-start mb-6">
                        <span class="bg-gray-100/80 text-gray-700 text-[10px] px-3 py-1.5 rounded-lg font-extrabold uppercase tracking-widest border border-gray-200/50 shadow-sm relative z-10 pointer-events-none">
                            {{ $project->status }}
                        </span>
                        
                        <div class="flex items-center gap-2 relative z-20">
                            
                            @role('Founder|Co-Founder|HR')
                            {{-- TOMBOL EDIT PROYEK --}}
                            <button type="button" @click.prevent="
                                openEditModal = true;
                                editUrl = '{{ route('project.update', $project->id) }}';
                                form.name = '{{ addslashes($project->name) }}';
                                form.client_name = '{{ addslashes($project->client->name ?? '') }}';
                                form.client_phone = '{{ addslashes($project->client->phone ?? '') }}';
                                form.pic_id = '{{ $project->pic_id }}';
                                form.finder_id = '{{ $project->finder_id }}';
                                form.total_price = '{{ $project->total_price }}';
                                form.start_date = '{{ \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') }}';
                                form.deadline = '{{ \Carbon\Carbon::parse($project->deadline)->format('Y-m-d') }}';
                            " class="w-8 h-8 rounded-full bg-white border border-indigo-100 text-indigo-500 hover:bg-indigo-500 hover:text-white flex items-center justify-center transition-colors shadow-sm cursor-pointer" title="Edit Proyek">
                                <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>

                            {{-- TOMBOL HAPUS PROYEK --}}
                            <form action="{{ route('project.destroy', $project->id) }}" method="POST" onsubmit="return confirm('⚠️ PERINGATAN: Apakah Anda yakin ingin membuang proyek ini?');" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-full bg-white border border-rose-100 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-colors shadow-sm cursor-pointer" title="Hapus Proyek Ini">
                                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @endrole

                            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 border border-gray-200 group-hover:translate-x-1 pointer-events-none">
                                <span class="text-gray-900 font-bold">&rarr;</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative z-10 pointer-events-none">
                        <h3 class="text-2xl font-extrabold text-gray-900 group-hover:text-indigo-900 transition-colors mb-1.5 line-clamp-1">
                            {{ $project->name }}
                        </h3>
                        <p class="text-sm font-bold text-gray-500 mb-8 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            {{ $project->client->name ?? 'Internal / R&D' }}
                        </p>
                        
                        <div class="mb-6">
                            <div class="flex justify-between text-xs mb-2">
                                <span class="font-extrabold text-gray-400 uppercase tracking-widest text-[10px]">Penyelesaian</span>
                                <span class="font-extrabold text-gray-900 text-xs">{{ $project->progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden border border-gray-200/50 shadow-inner">
                                <div class="bg-gradient-to-r from-gray-700 to-black h-full rounded-full transition-all duration-1000 ease-out relative" style="width: {{ $project->progress }}%">
                                    <div class="absolute top-0 right-0 bottom-0 w-10 bg-gradient-to-r from-transparent to-white/20"></div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-5 border-t border-gray-100 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border-2 border-white shadow-md flex items-center justify-center text-xs font-extrabold text-gray-700 relative group-hover:scale-110 transition-transform duration-300">
                                    {{ substr($project->pic->name ?? 'U', 0, 1) }}
                                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                                </div>
                                <div>
                                    <p class="text-[9px] font-extrabold text-gray-400 uppercase tracking-widest leading-none mb-1">Project Leader</p>
                                    <p class="text-xs font-extrabold text-gray-900">{{ explode(' ', $project->pic->name)[0] ?? 'Unassigned' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white/60 backdrop-blur-md rounded-[2.5rem] p-20 text-center border-2 border-gray-200 border-dashed shadow-sm">
                    <div class="w-24 h-24 bg-gradient-to-br from-gray-50 to-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-white">
                        <span class="text-4xl">🚀</span>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Kanvas Masih Kosong</h3>
                    <p class="text-gray-500 max-w-md mx-auto font-medium text-sm">Belum ada mahakarya yang berjalan pada periode ini. Klik tombol "New Project" di atas untuk memulai inisiasi.</p>
                </div>
            @endforelse
        </div>
    </main>

    {{-- MODAL CREATE PROJECT --}}
    <div x-cloak x-show="openCreateModal" class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
        <div x-show="openCreateModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" @click="openCreateModal = false"></div>
        <div x-show="openCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative z-[101] bg-white rounded-[2.5rem] p-8 shadow-[0_30px_60px_rgba(0,0,0,0.25)] w-full max-w-3xl border border-gray-100 max-h-[90vh] overflow-y-auto" style="scrollbar-width: thin;">
            
            <div class="flex justify-between items-start pb-6 border-b border-gray-100 mb-6 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Inisiasi Proyek Baru</h3>
                    <p class="text-sm text-gray-500 font-medium mt-1">Lengkapi parameter di bawah untuk meresmikan workspace klien.</p>
                </div>
                <button type="button" @click="openCreateModal = false" class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 text-gray-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            @if(!$activePeriod)
                <div class="bg-red-50 border border-red-100 rounded-2xl p-6 flex gap-4 items-start">
                    <div class="bg-white p-2 rounded-xl shadow-sm border border-red-100/50"><span class="text-2xl">⚠️</span></div>
                    <div>
                        <h4 class="text-red-800 font-extrabold mb-1">Sistem Terkunci</h4>
                        <p class="text-sm text-red-600 font-medium leading-relaxed">Anda tidak dapat membuat proyek baru karena tidak ada Periode Kerja yang aktif.</p>
                    </div>
                </div>
            @else
            <form action="{{ route('project.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Nama Proyek</label>
                        <input type="text" name="name" required class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-extrabold text-gray-900 focus:border-indigo-500 outline-none">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Nama Klien</label>
                            <input type="text" name="client_name" required class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-gray-900 focus:border-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">No. WhatsApp</label>
                            <input type="text" name="client_phone" class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-gray-900 focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Project Leader (PIC)</label>
                        <select name="pic_id" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                            <option value="">-- Pilih Penanggung Jawab --</option>
                            @foreach($team as $member) <option value="{{ $member->id }}">{{ $member->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Finder (Target KPI)</label>
                        <select name="finder_id" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                            <option value="">-- Siapa Pencari Klien Ini? --</option>
                            @foreach($team as $member) <option value="{{ $member->id }}">{{ $member->name }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Nilai Kontrak (Rp)</label>
                        <input type="number" name="total_price" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">DP Awal (Rp)</label>
                        <input type="number" name="dp_amount" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Mulai</label>
                        <input type="date" name="start_date" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Deadline</label>
                        <input type="date" name="deadline" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white rounded-2xl py-4 font-extrabold text-sm transition-all duration-300 mt-6 shadow-[0_8px_20px_rgba(0,0,0,0.15)] hover:-translate-y-0.5">Mulai Kerjakan Proyek &rarr;</button>
            </form>
            @endif
        </div>
    </div>

    {{-- MODAL EDIT PROJECT --}}
    <div x-cloak x-show="openEditModal" class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
        <div x-show="openEditModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" @click="openEditModal = false"></div>
        <div x-show="openEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative z-[101] bg-white rounded-[2.5rem] p-8 shadow-[0_30px_60px_rgba(0,0,0,0.25)] w-full max-w-3xl border border-gray-100 max-h-[90vh] overflow-y-auto" style="scrollbar-width: thin;">
            
            <div class="flex justify-between items-start pb-6 border-b border-gray-100 mb-6 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Edit Proyek Aktif</h3>
                    <p class="text-sm text-gray-500 font-medium mt-1">Perbarui detail operasional dan timeline proyek.</p>
                </div>
                <button type="button" @click="openEditModal = false" class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 text-gray-400 hover:text-indigo-500 hover:bg-indigo-50 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form :action="editUrl" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 space-y-5">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Nama Proyek</label>
                        <input type="text" name="name" x-model="form.name" required class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-extrabold text-gray-900 focus:border-indigo-500 outline-none">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Nama Klien</label>
                            <input type="text" name="client_name" x-model="form.client_name" required class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-gray-900 focus:border-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">No. WhatsApp</label>
                            <input type="text" name="client_phone" x-model="form.client_phone" class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-gray-900 focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Project Leader (PIC)</label>
                        <select name="pic_id" x-model="form.pic_id" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                            @foreach($team as $member) <option value="{{ $member->id }}">{{ $member->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Finder</label>
                        <select name="finder_id" x-model="form.finder_id" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                            @foreach($team as $member) <option value="{{ $member->id }}">{{ $member->name }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Total Nilai Kontrak (Rp)</label>
                        <input type="number" name="total_price" x-model="form.total_price" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                    </div>
                    {{-- DP Input dihilangkan untuk keamanan pembukuan. --}}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Timeline Mulai</label>
                        <input type="date" name="start_date" x-model="form.start_date" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Timeline Deadline</label>
                        <input type="date" name="deadline" x-model="form.deadline" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl py-4 font-extrabold text-sm transition-all duration-300 mt-6 shadow-[0_8px_20px_rgba(79,70,229,0.25)] hover:-translate-y-0.5">Simpan Perubahan &rarr;</button>
            </form>
        </div>
    </div>

    @livewireScripts
</body>
</html>