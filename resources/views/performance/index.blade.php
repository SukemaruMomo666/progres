<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI & Evaluasi Tim - XGrow Workspace</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; } 
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F4F7F6] text-gray-800 antialiased relative overflow-x-hidden" x-data="{ openResetModal: false }">

    <div class="absolute top-0 left-0 w-full h-[400px] bg-gradient-to-b from-gray-200/60 via-gray-100/30 to-transparent -z-10 pointer-events-none"></div>
    <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-gradient-to-br from-rose-100/30 to-transparent blur-3xl -z-10 pointer-events-none"></div>

    <x-navbar title="Analytics & KPI Tim" />

    <main class="max-w-5xl mx-auto px-8 py-12 relative z-10">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
            <div>
                <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Evaluasi & KPI Tim</h2>
                <p class="text-gray-500 mt-2 font-medium text-sm">Status nyata kewajiban kontribusi minimal mitra per anggota tim pada periode ini.</p>
            </div>
            
            <button type="button" @click="openResetModal = true" class="relative px-6 py-2.5 bg-rose-600 text-white rounded-xl font-bold text-sm shadow-[0_8px_20px_rgba(225,29,72,0.2)] hover:shadow-[0_12px_25px_rgba(225,29,72,0.3)] hover:-translate-y-0.5 transition-all duration-300 group overflow-hidden cursor-pointer">
                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                <span class="relative z-10 flex items-center gap-2">🔄 Tutup & Reset Periode</span>
            </button>
        </div>

        <div class="space-y-5">
            @forelse($teamData as $member)
            <div class="bg-white/70 backdrop-blur-xl border border-gray-100 rounded-[2rem] p-7 shadow-[0_8px_30px_rgba(0,0,0,0.03)] hover:shadow-[0_15px_35px_rgba(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-300 flex flex-col md:flex-row md:items-center justify-between gap-6 group relative overflow-hidden">
                
                <div class="absolute top-0 right-0 w-48 h-full bg-gradient-to-l from-gray-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>

                <div class="flex items-center gap-4 min-w-[220px] max-w-[280px] shrink-0">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-800 to-black text-white font-extrabold flex items-center justify-center text-base shadow-md border-2 border-white ring-2 ring-gray-100 group-hover:scale-105 transition-transform shrink-0">
                        {{ substr($member->name, 0, 1) }}
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="font-extrabold text-gray-900 text-base leading-tight group-hover:text-indigo-900 transition-colors truncate" title="{{ $member->name }}">
                            {{ $member->name }}
                        </h4>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 truncate">{{ $member->roles->pluck('name')->first() ?? 'Staff' }}</p>
                    </div>
                </div>

                <div class="flex-1 w-full">
                    <div class="flex justify-between items-center text-xs mb-2">
                        <span class="font-bold text-gray-500 uppercase tracking-wider text-[10px]">
                            Pencapaian: <span class="text-gray-900">{{ $member->total_mitra }}</span> / {{ $activePeriod->target_mitra_per_user ?? 1 }} Mitra
                        </span>
                        <span class="font-extrabold text-gray-900">{{ $member->kpi_progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden border border-gray-200/50 shadow-inner">
                        <div class="h-full rounded-full transition-all duration-1000 ease-out relative 
                            @if($member->kpi_progress >= 100) bg-gradient-to-r from-emerald-400 to-emerald-500 
                            @else bg-gradient-to-r from-amber-400 to-amber-500 @endif" 
                            style="width: {{ $member->kpi_progress }}%">
                            <div class="absolute top-0 right-0 bottom-0 w-10 bg-gradient-to-r from-transparent to-white/30"></div>
                        </div>
                    </div>
                </div>

                <div class="text-right min-w-[130px] flex justify-end shrink-0">
                    <span class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-[10px] font-extrabold tracking-widest uppercase border shadow-sm
                        @if($member->status_kpi === 'Lolos Target') bg-emerald-50 text-emerald-600 border-emerald-100
                        @else bg-amber-50 text-amber-600 border-amber-100 @endif">
                        {{ $member->status_kpi }}
                    </span>
                </div>
            </div>
            @empty
            <div class="bg-white/60 backdrop-blur-md rounded-[2.5rem] p-16 text-center border-2 border-gray-200 border-dashed shadow-sm">
                <div class="w-24 h-24 bg-gradient-to-br from-gray-50 to-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-white">
                    <span class="text-4xl">👥</span>
                </div>
                <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Data Tim Kosong</h3>
                <p class="text-gray-500 max-w-md mx-auto font-medium text-sm">Tidak ada data anggota tim atau periode aktif saat ini. Buka periode baru untuk memulai sistem *tracking*.</p>
            </div>
            @endforelse
        </div>
    </main>

    <div x-cloak x-show="openResetModal" class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
        <div x-show="openResetModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" @click="openResetModal = false"></div>
        
        <div x-show="openResetModal" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="relative z-[101] transform overflow-hidden rounded-[2.5rem] bg-white p-8 shadow-[0_30px_60px_rgba(0,0,0,0.25)] transition-all w-full max-w-md border border-gray-100">
            
            <div class="flex justify-between items-start pb-5 border-b border-gray-100 mb-6">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Buka Kuartal Baru</h3>
                    <p class="text-sm text-gray-500 font-medium mt-1">Inisiasi periode operasional baru.</p>
                </div>
                <button type="button" @click="openResetModal = false" class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 text-gray-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-5 mb-6 flex gap-4 items-start shadow-sm">
                <div class="bg-white p-2.5 rounded-xl shadow-sm border border-rose-100/50">
                    <span class="text-xl">⚠️</span>
                </div>
                <p class="text-xs text-rose-700 leading-relaxed font-bold">
                    Tindakan ini akan menonaktifkan periode yang sedang berjalan dan mengatur ulang seluruh hitungan KPI target mitra tim ke 0 untuk lembaran baru.
                </p>
            </div>

            <form action="{{ route('performance.close') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Nama Periode Baru</label>
                    <input type="text" name="new_period_name" required placeholder="Contoh: Kuartal 2 (Q2) - 2026" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-bold text-gray-900 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none shadow-sm">
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Tgl Mulai</label>
                        <input type="date" name="start_date" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-extrabold text-gray-900 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none cursor-pointer shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Tgl Berakhir</label>
                        <input type="date" name="end_date" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-extrabold text-gray-900 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none cursor-pointer shadow-sm">
                    </div>
                </div>

                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-2xl py-4 font-extrabold text-sm transition-all duration-300 mt-4 shadow-[0_8px_20px_rgba(225,29,72,0.2)] hover:shadow-[0_12px_25px_rgba(225,29,72,0.3)] hover:-translate-y-0.5 cursor-pointer">
                    Resmikan & Reset Tim &rarr;
                </button>
            </form>
        </div>
    </div>

</body>
</html>