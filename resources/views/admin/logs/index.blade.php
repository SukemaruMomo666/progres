<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - XGrow Workspace</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7F6] text-gray-800 antialiased">
    <x-navbar title="Audit Trail & Jejak Digital" />

    <main class="max-w-7xl mx-auto px-8 py-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
            <div>
                <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">System Audit Logs</h2>
                <p class="text-gray-500 mt-2 text-sm font-medium">Memantau seluruh aktivitas forensik digital anggota tim XGrow Studio secara transparan.</p>
            </div>
            <div class="px-5 py-3 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">Total Record</p>
                <p class="text-2xl font-black text-gray-900">{{ $logs->total() }} <span class="text-sm text-gray-400">Entries</span></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm mb-8 flex flex-col md:flex-row gap-4 items-center">
            <form action="{{ route('admin.logs.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 w-full">
                <div class="flex-1">
                    <input type="text" name="activity" value="{{ request('activity') }}" placeholder="Cari aksi (Contoh: Hapus)..." class="w-full px-5 py-3 rounded-2xl border border-gray-200 text-sm font-medium focus:border-indigo-500 outline-none">
                </div>
                <div class="w-full md:w-48">
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full px-5 py-3 rounded-2xl border border-gray-200 text-sm font-medium focus:border-indigo-500 outline-none">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-extrabold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">Filter</button>
                    <a href="{{ route('admin.logs.index') }}" class="px-6 py-3 bg-gray-100 text-gray-600 rounded-2xl text-sm font-extrabold hover:bg-gray-200 transition-colors">Reset</a>
                </div>
            </form>
        </div>

        <div class="bg-white/80 backdrop-blur-xl border border-gray-100 rounded-[2.5rem] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">
                            <th class="px-8 py-5">Waktu</th>
                            <th class="px-8 py-5">Eksekutor</th>
                            <th class="px-8 py-5">Aktivitas</th>
                            <th class="px-8 py-5">Detail Deskripsi</th>
                            <th class="px-8 py-5">IP & Device</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm font-medium text-gray-600">
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-8 py-5 text-gray-400 text-xs whitespace-nowrap">
                                {{ $log->created_at->translatedFormat('d M Y - H:i:s') }}
                            </td>
                            <td class="px-8 py-5 font-extrabold text-gray-950 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-black">
                                        {{ substr($log->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    {{ $log->user->name ?? 'System' }}
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                @php
                                    $badgeColor = 'bg-slate-900';
                                    if(str_contains($log->activity, 'Hapus')) $badgeColor = 'bg-rose-500';
                                    elseif(str_contains($log->activity, 'Login')) $badgeColor = 'bg-emerald-500';
                                    elseif(str_contains($log->activity, 'Update') || str_contains($log->activity, 'Edit')) $badgeColor = 'bg-amber-500';
                                @endphp
                                <span class="px-3 py-1 rounded-lg text-[11px] font-black uppercase tracking-wider {{ $badgeColor }} text-white shadow-sm">
                                    {{ $log->activity }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-gray-700 max-w-sm">
                                {{ $log->description }}
                            </td>
                            <td class="px-8 py-5 text-xs text-gray-400">
                                <span class="block font-bold text-gray-500">{{ $log->ip_address }}</span>
                                {{ Str::limit($log->user_agent, 20) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center text-gray-400 font-bold">
                                📜 Belum ada rekam jejak aktivitas pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-6 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        </div>
    </main>
</body>
</html>