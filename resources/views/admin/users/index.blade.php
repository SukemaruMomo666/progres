<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User & Roles Management - XGrow Workspace</title>
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
<body class="bg-[#F4F7F6] text-gray-800 antialiased relative overflow-x-hidden" x-data="{ openPassModal: false, openCreateModal: false, activeUser: '', resetUrl: '' }">

    <div class="absolute top-0 left-0 w-full h-[400px] bg-gradient-to-b from-slate-200/60 via-gray-100/30 to-transparent -z-10 pointer-events-none"></div>
    <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-gradient-to-br from-indigo-100/30 to-transparent blur-3xl -z-10 pointer-events-none"></div>

    <x-navbar title="Otoritas SDM & Pengguna" />

    <main class="max-w-7xl mx-auto px-8 py-12 relative z-10">
        
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>🎉</span> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-sm space-y-1.5">
                <p class="font-extrabold text-rose-900">🚨 Gagal Memproses Permintaan:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs font-bold text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
            <div>
                <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Manajemen Pengguna</h2>
                <p class="text-gray-500 mt-2 font-medium text-sm">Kelola akun tim XGrow Studio, daftarkan anggota baru, dan delegasikan enkripsi Spatie Roles.</p>
            </div>
            <button type="button" @click="openCreateModal = true" class="relative px-6 py-2.5 bg-gray-900 text-white rounded-xl font-bold text-sm shadow-[0_8px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_12px_25px_rgba(0,0,0,0.25)] hover:-translate-y-0.5 transition-all duration-300 group overflow-hidden cursor-pointer">
                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                <span class="relative z-10 flex items-center gap-2"><span class="text-lg leading-none">+</span> Tambah Anggota Tim</span>
            </button>
        </div>

        <div class="bg-white/80 backdrop-blur-xl border border-gray-100 rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 bg-white/50">
                <h3 class="text-lg font-extrabold text-gray-900">Direktori Anggota Tim Terdaftar</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">
                            <th class="px-8 py-5 whitespace-nowrap">Anggota Tim</th>
                            <th class="px-8 py-5">Surel / Email</th>
                            <th class="px-8 py-5">Hak Akses Terdaftar (Spatie)</th>
                            <th class="px-8 py-5 text-right">Tindakan Keamanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-50/40 transition-colors duration-200">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-gray-900 to-gray-700 flex items-center justify-center font-extrabold text-white text-sm shadow-md border-2 border-white ring-2 ring-gray-100">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-gray-900 text-base leading-tight">{{ $user->name }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">ID-USER: {{ $user->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-gray-600 font-semibold">{{ $user->email }}</td>
                            <td class="px-8 py-5">
                                <form action="{{ route('admin.users.update-role', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @php
                                        $currentRole = $user->roles->pluck('name')->first() ?? 'Staff';
                                        $badgeColor = match($currentRole) {
                                            'Founder' => 'bg-gray-900 text-white border-gray-900',
                                            'Co-Founder' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                            'HR' => 'bg-rose-50 text-rose-600 border-rose-100',
                                            default => 'bg-gray-50 text-gray-600 border-gray-200'
                                        };
                                    @endphp
                                    <select name="role" onchange="this.form.submit()" class="text-xs font-extrabold uppercase tracking-wider {{ $badgeColor }} border px-3 py-2 rounded-xl cursor-pointer outline-none shadow-sm">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ $currentRole === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-8 py-5 text-right whitespace-nowrap">
                                <button type="button" @click="openPassModal = true; activeUser = '{{ $user->name }}'; resetUrl = '{{ route('admin.users.reset-password', $user->id) }}'" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-indigo-600 font-extrabold text-xs shadow-sm cursor-pointer">🔑 Ganti Sandi</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div x-cloak x-show="openCreateModal" class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4">
        <div x-show="openCreateModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" @click="openCreateModal = false"></div>
        <div x-show="openCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95" class="relative rounded-[2.5rem] bg-white p-8 shadow-2xl w-full max-w-lg border border-gray-100 z-[101]">
            <div class="flex justify-between items-start pb-5 border-b border-gray-100 mb-6">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">Daftarkan Anggota</h3>
                    <p class="text-sm text-gray-500 font-medium mt-0.5">Buat akses masuk untuk internal tim baru.</p>
                </div>
                <button type="button" @click="openCreateModal = false" class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 text-gray-400 hover:text-black flex items-center justify-center cursor-pointer font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Prabu Alam" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-semibold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Alamat Email (Untuk Login)</label>
                    <input type="email" name="email" required placeholder="name@xgrow.com" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-semibold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Kata Sandi Awal</label>
                        <input type="password" name="password" required placeholder="••••••••" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-semibold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Hak Akses Role</label>
                        <select name="role" required class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-extrabold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all cursor-pointer">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white rounded-2xl py-4 font-extrabold text-sm shadow-[0_8px_20px_rgba(0,0,0,0.15)] hover:-translate-y-0.5 transition-all duration-300 cursor-pointer mt-4">Resmikan Akun Anggota &rarr;</button>
            </form>
        </div>
    </div>

    <div x-cloak x-show="openPassModal" class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4">
        <div x-show="openPassModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" @click="openPassModal = false"></div>
        <div x-show="openPassModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative transform overflow-hidden rounded-[2.5rem] bg-white p-8 shadow-2xl w-full max-w-md border border-gray-100 z-[101]">
            <div class="flex justify-between items-start pb-5 border-b border-gray-100 mb-6">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Ganti Sandi</h3>
                    <p class="text-sm text-gray-500 font-medium mt-0.5">Mengubah kredensial akun: <span class="font-extrabold text-indigo-600" x-text="activeUser"></span></p>
                </div>
                <button type="button" @click="openPassModal = false" class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 text-gray-400 hover:text-black flex items-center justify-center cursor-pointer font-bold">&times;</button>
            </div>
            <form :action="resetUrl" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Kata Sandi Baru</label>
                    <input type="password" name="password" required placeholder="••••••••" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-semibold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="block w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-semibold text-gray-900 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                </div>
                <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white rounded-2xl py-4 font-extrabold text-sm shadow-[0_8px_20px_rgba(0,0,0,0.15)] hover:-translate-y-0.5 transition-all duration-300 cursor-pointer mt-4">Perbarui Kredensial Akses &rarr;</button>
            </form>
        </div>
    </div>

</body>
</html>