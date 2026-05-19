<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI & Evaluasi Tim - XGrow</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-[#F8F9FA] text-gray-800 antialiased" x-data="{ openResetModal: false }">

    <nav class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center sticky top-0 z-40 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-black">&larr; Dashboard</a>
            <h1 class="text-xl font-bold tracking-tight text-gray-900">Evaluasi & KPI Tim</h1>
        </div>
        <span class="text-sm font-semibold bg-red-50 text-red-700 px-3 py-1.5 rounded-lg border border-red-100">Akses Manajemen Terbatas 🔐</span>
    </nav>

    <main class="max-w-5xl mx-auto px-8 py-10">
        
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Target Pencarian Mitra</h2>
                <p class="text-sm text-gray-500 mt-1">Status nyata kewajiban kontribusi minimal 1 mitra per anggota tim pada periode ini.</p>
            </div>
            <button @click="openResetModal = true" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
                🔄 Tutup & Reset Periode
            </button>
        </div>

        <div class="space-y-4">
            @forelse($teamData as $member)
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.01)] flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4 min-w-[250px]">
                    <div class="w-12 h-12 rounded-full bg-black text-white font-bold flex items-center justify-center text-lg">
                        {{ substr($member->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 text-base">{{ $member->name }}</h4>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $member->roles->pluck('name')->first() ?? 'Staff' }}</p>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="flex justify-between items-center text-xs font-semibold mb-2">
                        <span class="text-gray-500">Progres Target ({{ $member->total_mitra }} / {{ $activePeriod->target_mitra_per_user }} Mitra)</span>
                        <span class="text-gray-900">{{ $member->kpi_progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-500 @if($member->kpi_progress >= 100) bg-emerald-500 @else bg-amber-500 @endif" style="width: {{ $member->kpi_progress }}%"></div>
                    </div>
                </div>

                <div class="text-right min-w-[120px]">
                    <span class="inline-block text-xs font-bold px-3 py-1.5 rounded-full @if($member->status_kpi === 'Lolos Target') bg-emerald-50 text-emerald-700 border border-emerald-100 @else bg-amber-50 text-amber-700 border border-amber-100 @endif">
                        {{ $member->status_kpi }}
                    </span>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl p-10 text-center border border-gray-100">
                <p class="text-gray-500">Tidak ada data tim atau periode aktif saat ini.</p>
            </div>
            @endforelse
        </div>
    </main>

    <div x-show="openResetModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="openResetModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all sm:w-full sm:max-w-md border border-gray-100">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Buka Kuartal Periode Baru</h3>
                    <button @click="openResetModal = false" class="text-gray-400 hover:text-black font-semibold text-xl">&times;</button>
                </div>
                
                <p class="text-xs text-red-600 leading-relaxed mb-4 font-medium bg-red-50 border border-red-100 p-3 rounded-lg">
                    ⚠️ PERINGATAN: Tindakan ini akan menonaktifkan periode saat ini dan mengatur ulang hitungan KPI target mitra tim untuk memulai lembaran kerja yang baru.
                </p>

                <form action="{{ route('performance.close') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Periode Baru</label>
                        <input type="text" name="new_period_name" required placeholder="Contoh: Kuartal 2 (Q2) - 2026" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                            <input type="date" name="start_date" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Berakhir</label>
                            <input type="date" name="end_date" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white rounded-lg py-2.5 font-semibold text-sm transition mt-2 shadow-sm">
                        Resmikan & Reset Kerja Tim
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>