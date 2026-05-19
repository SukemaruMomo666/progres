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
        <div class="mb-10">
            <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">System Audit Logs</h2>
            <p class="text-gray-500 mt-2 text-sm font-medium">Memantau seluruh aktivitas forensik digital anggota tim XGrow Studio secara transparan.</p>
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
                                👤 {{ $log->user->name ?? 'System' }}
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-lg text-[11px] font-black uppercase tracking-wider bg-slate-900 text-white shadow-sm">
                                    {{ $log->activity }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-gray-700 max-w-xs md:max-w-md break-words">
                                {{ $log->description }}
                            </td>
                            <td class="px-8 py-5 text-xs text-gray-400 truncate max-w-[150px]" title="{{ $log->user_agent }}">
                                <span class="block font-bold text-gray-500">{{ $log->ip_address }}</span>
                                {{ Str::limit($log->user_agent, 30) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center text-gray-400 font-bold">
                                📜 Belum ada rekam jejak aktivitas apa pun pada sistem ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>