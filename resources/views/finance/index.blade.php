<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management - XGrow Workspace</title>
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
<body class="bg-[#F4F7F6] text-gray-800 antialiased relative overflow-x-hidden" x-data="{ openModal: false }">

    <div class="absolute top-0 left-0 w-full h-[400px] bg-gradient-to-b from-gray-200/60 via-gray-100/30 to-transparent -z-10 pointer-events-none"></div>
    <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-gradient-to-br from-emerald-100/30 to-transparent blur-3xl -z-10 pointer-events-none"></div>

    <x-navbar title="Buku Kas Terproteksi" />

    <main class="max-w-7xl mx-auto px-8 py-12 relative z-10">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
            <div>
                <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Buku Kas Utama</h2>
                <p class="text-gray-500 mt-2 font-medium text-sm">Rekapitulasi arus kas studio pada periode <span class="font-bold text-gray-700">{{ $activePeriod->name ?? 'Nonaktif' }}</span>.</p>
            </div>
            
            <button type="button" @click="openModal = true" class="relative px-6 py-3 bg-gray-900 text-white rounded-2xl font-extrabold text-sm shadow-[0_8px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_12px_25px_rgba(0,0,0,0.25)] hover:-translate-y-0.5 transition-all duration-300 group overflow-hidden cursor-pointer">
                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                <span class="relative z-10 flex items-center gap-2">
                    <span class="text-lg leading-none">+</span> Catat Transaksi Baru
                </span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-gradient-to-br from-gray-900 to-black rounded-[2rem] p-7 shadow-[0_10px_40px_rgba(0,0,0,0.15)] relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-bl-full group-hover:scale-110 transition-transform duration-700"></div>
                <p class="text-[11px] font-extrabold text-gray-400 uppercase tracking-widest mb-2 relative z-10">Total Saldo Bersih</p>
                <h3 class="text-3xl font-extrabold text-white mt-1 relative z-10">Rp {{ number_format($balance, 0, ',', '.') }}</h3>
            </div>
            
            <div class="bg-white/80 backdrop-blur-md rounded-[2rem] p-7 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>
                <p class="text-[11px] font-extrabold text-emerald-500 uppercase tracking-widest mb-2">Total Pemasukan</p>
                <h3 class="text-3xl font-extrabold text-gray-900 mt-1">Rp {{ number_format($income, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center gap-2 text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 bg-emerald-50 border border-emerald-100/50 w-fit px-3 py-1.5 rounded-lg shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    Income
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-md rounded-[2rem] p-7 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute top-0 right-0 w-1.5 h-full bg-rose-500"></div>
                <p class="text-[11px] font-extrabold text-rose-500 uppercase tracking-widest mb-2">Total Pengeluaran</p>
                <h3 class="text-3xl font-extrabold text-gray-900 mt-1">Rp {{ number_format($expense, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center gap-2 text-[10px] font-extrabold uppercase tracking-widest text-rose-600 bg-rose-50 border border-rose-100/50 w-fit px-3 py-1.5 rounded-lg shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    Expense
                </div>
            </div>
        </div>

        <div class="mb-12">
            <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-6">Status Tagihan Proyek (Piutang)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($projects as $p)
                <div class="bg-white/70 backdrop-blur-xl rounded-[2rem] p-7 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] hover:-translate-y-1 hover:bg-white transition-all duration-400 relative overflow-hidden group">
                    
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-gray-50 to-transparent rounded-bl-full opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>

                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h4 class="font-extrabold text-gray-900 text-xl leading-tight line-clamp-1 group-hover:text-indigo-900 transition-colors">{{ $p->name }}</h4>
                            <p class="text-[11px] font-extrabold text-gray-400 uppercase tracking-widest mt-1.5">Kontrak: Rp {{ number_format($p->total_price, 0, ',', '.') }}</p>
                        </div>
                        <span class="px-3 py-1.5 rounded-lg text-[9px] font-extrabold uppercase tracking-widest border shadow-sm
                            {{ $p->payment_percentage >= 100 ? 'bg-emerald-50 text-emerald-600 border-emerald-100/50' : 'bg-amber-50 text-amber-600 border-amber-100/50' }}">
                            {{ $p->payment_percentage >= 100 ? 'LUNAS' : 'PENDING' }}
                        </span>
                    </div>

                    <div class="mb-5">
                        <div class="flex justify-between text-xs mb-2">
                            <span class="font-extrabold text-gray-400 uppercase tracking-widest text-[9px]">Telah Dibayar ({{ $p->payment_percentage }}%)</span>
                            <span class="font-extrabold text-gray-900">Rp {{ number_format($p->total_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden border border-gray-200/50 shadow-inner">
                            <div class="h-full rounded-full transition-all duration-1000 ease-out relative {{ $p->payment_percentage >= 100 ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : 'bg-gradient-to-r from-amber-400 to-amber-500' }}" style="width: {{ $p->payment_percentage }}%">
                                <div class="absolute top-0 right-0 bottom-0 w-10 bg-gradient-to-r from-transparent to-white/30"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5 mt-5 border-t border-gray-100 flex justify-between items-center text-xs">
                        <span class="font-extrabold text-gray-400 uppercase tracking-widest text-[9px]">Sisa Tagihan</span>
                        <span class="font-extrabold text-sm px-3 py-1.5 rounded-lg {{ $p->remaining_payment > 0 ? 'bg-rose-50 text-rose-600 border border-rose-100/50' : 'bg-emerald-50 text-emerald-600 border border-emerald-100/50' }}">
                            Rp {{ number_format(max(0, $p->remaining_payment), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="col-span-full bg-white/60 backdrop-blur-md rounded-[2.5rem] p-12 text-center border-2 border-gray-200 border-dashed shadow-sm">
                    <div class="w-16 h-16 bg-gradient-to-br from-gray-50 to-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 border border-white shadow-inner">
                        <span class="text-2xl">🧾</span>
                    </div>
                    <h3 class="text-lg font-extrabold text-gray-900 mb-1">Belum Ada Piutang</h3>
                    <p class="text-gray-500 max-w-md mx-auto font-medium text-sm">Tidak ada proyek aktif yang berjalan pada periode ini untuk dipantau tagihannya.</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl border border-gray-100 rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="px-8 py-7 border-b border-gray-100/80 bg-white/50">
                <h3 class="text-xl font-extrabold text-gray-900">Riwayat Sirkulasi Kas</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">
                            <th class="px-8 py-5 whitespace-nowrap">Tanggal</th>
                            <th class="px-8 py-5">Kategori & Keterangan</th>
                            <th class="px-8 py-5">Kaitan Proyek</th>
                            <th class="px-8 py-5 text-right">Nominal Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50/80 text-sm">
                        @forelse($transactions as $tx)
                        <tr class="hover:bg-gray-50/80 transition-colors duration-200 group">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="font-extrabold text-gray-700 bg-white px-3 py-1.5 rounded-lg border border-gray-100 shadow-sm">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <span class="font-extrabold text-gray-900 block mb-1 text-base">{{ $tx->category }}</span>
                                <span class="text-xs font-medium text-gray-500 block max-w-md truncate">{{ $tx->description }}</span>
                            </td>
                            <td class="px-8 py-6">
                                @if($tx->project)
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100/80 border border-gray-200/80 text-[11px] font-extrabold text-gray-700 uppercase tracking-wider">
                                        📁 {{ $tx->project->name }}
                                    </span>
                                @else
                                    <span class="text-[11px] font-extrabold text-gray-400 uppercase tracking-widest border border-dashed border-gray-200 px-3 py-1.5 rounded-lg">Internal</span>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right whitespace-nowrap">
                                @if($tx->type === 'Income')
                                    <span class="inline-block px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 font-black text-sm border border-emerald-100/50 shadow-sm group-hover:scale-105 transition-transform">
                                        + Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="inline-block px-4 py-2 rounded-xl bg-rose-50 text-rose-600 font-black text-sm border border-rose-100/50 shadow-sm group-hover:scale-105 transition-transform">
                                        - Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="w-20 h-20 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                                    <span class="text-3xl">💸</span>
                                </div>
                                <h4 class="text-gray-900 font-extrabold text-lg mb-1">Belum Ada Transaksi</h4>
                                <p class="text-gray-400 text-sm font-medium">Sirkulasi keuangan pada periode ini masih kosong.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div x-cloak x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 sm:p-6">
        
        <div x-show="openModal" 
             x-transition.opacity.duration.300ms 
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" 
             @click="openModal = false"></div>
        
        <div x-show="openModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="relative z-[101] transform overflow-hidden rounded-[2.5rem] bg-white p-8 shadow-[0_30px_60px_rgba(0,0,0,0.25)] transition-all w-full max-w-2xl border border-gray-100 max-h-[90vh] overflow-y-auto" style="scrollbar-width: thin;">
            
            <div class="flex justify-between items-start pb-6 border-b border-gray-100 mb-6 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Pencatatan Kas</h3>
                    <p class="text-sm text-gray-500 font-medium mt-1">Masukkan data sirkulasi finansial secara presisi.</p>
                </div>
                <button type="button" @click="openModal = false" class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 text-gray-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('finance.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Tipe Arus Kas</label>
                            <select name="type" required class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-extrabold text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer outline-none shadow-sm">
                                <option value="Income">🟢 Pemasukan (Income)</option>
                                <option value="Expense">🔴 Pengeluaran (Expense)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Tanggal Transaksi</label>
                            <input type="date" name="transaction_date" required class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-extrabold text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer outline-none shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Kategori Transaksi</label>
                        <input type="text" name="category" placeholder="Contoh: Pembayaran Server / Pelunasan Klien" required class="block w-full rounded-2xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-bold text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Nominal Uang (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-5 top-3.5 text-gray-400 font-extrabold text-sm">Rp</span>
                            <input type="number" name="amount" placeholder="0" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 pl-12 pr-5 py-3.5 text-sm font-extrabold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Hubungkan ke Proyek (Opsional)</label>
                        <select name="project_id" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 text-sm font-extrabold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer outline-none">
                            <option value="">-- Biarkan kosong jika Kas Internal --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Catatan Ekstra</label>
                    <textarea name="description" rows="3" required placeholder="Tuliskan alasan pengeluaran/pemasukan ini secara detail..." class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 text-sm font-medium text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white rounded-2xl py-4 font-extrabold text-sm transition-all duration-300 mt-4 shadow-[0_8px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_12px_30px_rgba(0,0,0,0.25)] hover:-translate-y-0.5 cursor-pointer">
                    Simpan Arsip Keuangan &rarr;
                </button>
            </form>
        </div>
    </div>

</body>
</html>