<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - XGrow Internal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-[#F8F9FA] text-gray-800 antialiased" x-data="{ openCreateModal: false }">

    <nav class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold tracking-tight text-gray-900">XGrow Workspace</h1>
            @if($activePeriod)
                <span class="bg-black text-white text-xs px-3 py-1 rounded-full font-medium">
                    Periode: {{ $activePeriod->name }}
                </span>
            @else
                <span class="bg-red-100 text-red-700 text-xs px-3 py-1 rounded-full font-medium">
                    Tidak ada periode aktif
                </span>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-gray-600">
                {{ Auth::user()->name }} ({{ Auth::user()->roles->pluck('name')->first() }})
            </span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold transition">Logout</button>
            </form>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-8 py-10">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Active Projects</h2>
                <p class="text-gray-500 mt-1">Pantau progres proyek berjalan untuk periode ini.</p>
            </div>
            
            @role('Founder|Co-Founder|HR')
            <div class="flex items-center gap-3">
                <a href="{{ route('finance.index') }}" class="border border-gray-200 hover:border-black bg-white text-gray-700 px-5 py-2.5 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                    Lihat Keuangan Studio 📊
                </a>
                <button @click="openCreateModal = true" class="bg-black hover:bg-gray-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
                    + New Project
                </button>
            </div>
            @endrole
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($projects as $project)
                <a href="{{ route('project.board', $project->id) }}" class="block bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] hover:-translate-y-1 transition duration-300 group">
                    <div class="flex justify-between items-start mb-4">
                        <span class="bg-gray-100 text-gray-600 text-xs px-2.5 py-1 rounded-md font-semibold">
                            {{ $project->status }}
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 group-hover:text-black mb-1">
                        {{ $project->name }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">Client: {{ $project->client->name ?? 'Internal/N/A' }}</p>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="font-medium text-gray-700">Progress</span>
                            <span class="font-bold text-black">{{ $project->progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-black h-2 rounded-full transition-all duration-500" style="width: {{ $project->progress }}%"></div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-xs font-bold text-gray-600">
                                {{ substr($project->pic->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="text-xs font-medium text-gray-500">PIC: {{ explode(' ', $project->pic->name)[0] ?? 'Unassigned' }}</span>
                        </div>
                        <span class="text-xs font-bold text-black group-hover:underline">Buka Board &rarr;</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-10 text-center border border-gray-100 border-dashed">
                    <p class="text-gray-500">Belum ada proyek yang berjalan di periode ini.</p>
                </div>
            @endforelse
        </div>
    </main>

    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="openCreateModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all sm:w-full sm:max-w-xl border border-gray-100">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Inisiasi Proyek Baru</h3>
                    <button @click="openCreateModal = false" class="text-gray-400 hover:text-black font-semibold text-xl">&times;</button>
                </div>

                @if(!$activePeriod)
                    <p class="text-sm text-red-600 font-medium">Anda tidak dapat menambahkan proyek karena tidak ada Periode Kerja yang sedang aktif saat ini.</p>
                @else
                <form action="{{ route('project.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Proyek / Aplikasi</label>
                        <input type="text" name="name" required placeholder="Contoh: Aplikasi RADAR" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Klien</label>
                        <select name="client_id" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                            <option value="">-- Pilih Klien Pemilik Proyek --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company ?? 'Personal' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">PIC (Project Leader)</label>
                            <select name="pic_id" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                                <option value="">-- Pilih Penanggung Jawab --</option>
                                @foreach($team as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Finder (Pencari Mitra)</label>
                            <select name="finder_id" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                                <option value="">-- Penghitungan Target KPI --</option>
                                @foreach($team as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nilai Proyek (Total Rp)</label>
                            <input type="number" name="total_price" required placeholder="0" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nominal DP Awal (Rp)</label>
                            <input type="number" name="dp_amount" value="0" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                            <input type="date" name="start_date" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deadline Akhir</label>
                            <input type="date" name="deadline" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white rounded-lg py-2.5 font-semibold text-sm transition mt-2 shadow-sm">
                        Resmikan & Buat Proyek Baru
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>