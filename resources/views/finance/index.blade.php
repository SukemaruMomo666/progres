<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management - XGrow</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-[#F8F9FA] text-gray-800 antialiased" x-data="{ openModal: false }">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center sticky top-0 z-40 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-black">&larr; Dashboard</a>
            <h1 class="text-xl font-bold tracking-tight text-gray-900">Buku Kas Studio</h1>
        </div>
        <span class="text-sm font-medium text-gray-600">Akses Keuangan Terproteksi 🔒</span>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-8 py-10">
        
        <!-- Grid Ringkasan Keuangan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <!-- Saldo Bersih -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Saldo Bersih</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-2">Rp {{ number_format($balance, 0, ',', '.') }}</h3>
            </div>
            <!-- Total Pemasukan -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider">Total Pemasukan (Income)</p>
                <h3 class="text-3xl font-bold text-emerald-600 mt-2">Rp {{ number_format($income, 0, ',', '.') }}</h3>
            </div>
            <!-- Total Pengeluaran -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                <p class="text-xs font-bold text-red-400 uppercase tracking-wider">Total Pengeluaran (Expense)</p>
                <h3 class="text-3xl font-bold text-red-600 mt-2">Rp {{ number_format($expense, 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Header Tabel -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Riwayat Transaksi</h2>
                <p class="text-sm text-gray-500 mt-0.5">Semua catatan finansial resmi pada periode aktif berjalan.</p>
            </div>
            <button @click="openModal = true" class="bg-black hover:bg-gray-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition">
                + Catat Transaksi
            </button>
        </div>

        <!-- Tabel Transaksi Finansial -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-[0_4px_25px_rgba(0,0,0,0.02)] overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Kategori / Deskripsi</th>
                        <th class="px-6 py-4">Kaitan Proyek</th>
                        <th class="px-6 py-4 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 text-gray-500 font-medium">
                            {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900 block">{{ $tx->category }}</span>
                            <span class="text-xs text-gray-400 block mt-0.5">{{ $tx->description }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $tx->project->name ?? 'Pengeluaran Umum/Internal' }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold {{ $tx->type === 'Income' ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $tx->type === 'Income' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400">Belum ada sirkulasi keuangan yang dicatat pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>

    <!-- MODAL SLICING INPUT TRANSAKSI -->
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="openModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all sm:w-full sm:max-w-lg border border-gray-100">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Pencatatan Arus Kas</h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-black font-semibold text-xl">&times;</button>
                </div>

                <form action="{{ route('finance.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tipe Arus Kas</label>
                        <select name="type" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                            <option value="Income">Pemasukan (Income)</option>
                            <option value="Expense">Pengeluaran (Expense)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kategori</label>
                            <input type="text" name="category" placeholder="Contoh: DP Proyek, Sewa Server" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal</label>
                            <input type="date" name="transaction_date" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Jumlah Uang (Rupiah)</label>
                        <input type="number" name="amount" placeholder="0" required class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Hubungkan dengan Proyek (Opsional)</label>
                        <select name="project_id" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black">
                            <option value="">-- Tidak dikaitkan ke Proyek --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Keterangan Tambahan</label>
                        <textarea name="description" rows="3" required placeholder="Catatan detail mengenai sirkulasi kas ini..." class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:ring-black"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white rounded-lg py-2.5 font-semibold text-sm transition mt-2 shadow-sm">
                        Simpan Catatan Finansial
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>