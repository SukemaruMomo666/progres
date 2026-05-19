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
<body class="bg-[#F4F7F6] text-gray-800 antialiased relative overflow-x-hidden" 
      x-data="{ 
        openPassModal: false, 
        openCreateModal: false, 
        openEditModal: false,
        activeUser: '', 
        resetUrl: '',
        updateUrl: '',
        editName: '',
        editEmail: ''
      }">

    <div class="absolute top-0 left-0 w-full h-[400px] bg-gradient-to-b from-slate-200/60 via-gray-100/30 to-transparent -z-10 pointer-events-none"></div>
    <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-gradient-to-br from-indigo-100/30 to-transparent blur-3xl -z-10 pointer-events-none"></div>

    <x-navbar title="Otoritas SDM & Pengguna" />

    <main class="max-w-7xl mx-auto px-8 py-12 relative z-10">
        
        {{-- Flash Messages --}}
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
            
            @role('Founder|Co-Founder|HR')
            <button type="button" @click="openCreateModal = true" class="relative px-6 py-2.5 bg-gray-900 text-white rounded-xl font-bold text-sm shadow-[0_8px_20px_rgba(0,0,0,0.15)] hover:shadow-[0_12px_25px_rgba(0,0,0,0.25)] hover:-translate-y-0.5 transition-all duration-300 group overflow-hidden cursor-pointer">
                <span class="relative z-10 flex items-center gap-2"><span class="text-lg leading-none">+</span> Tambah Anggota Tim</span>
            </button>
            @endrole
        </div>

        <div class="bg-white/80 backdrop-blur-xl border border-gray-100 rounded-[2.5rem] shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 bg-white/50">
                <h3 class="text-lg font-extrabold text-gray-900">Direktori Anggota Tim Terdaftar</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">
                            <th class="px-8 py-5">Anggota Tim</th>
                            <th class="px-8 py-5">Email</th>
                            <th class="px-8 py-5">Role</th>
                            <th class="px-8 py-5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-50/40 transition-colors duration-200">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-gray-900 to-gray-700 flex items-center justify-center font-extrabold text-white text-sm shadow-md border-2 border-white ring-2 ring-gray-100">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <p class="font-extrabold text-gray-900">{{ $user->name }}</p>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-gray-600 font-semibold">{{ $user->email }}</td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase bg-gray-50 text-gray-600 border-gray-200">
                                    {{ $user->roles->pluck('name')->first() ?? 'Staff' }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right flex items-center justify-end gap-2">
                                
                                {{-- Tombol Edit & Sandi: Terlihat untuk Admin ATAU Diri Sendiri --}}
                                @if(auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR']) || auth()->id() === $user->id)
                                    <button type="button" 
                                        @click="openEditModal = true; editName = '{{ $user->name }}'; editEmail = '{{ $user->email }}'; updateUrl = '{{ route('admin.users.update', $user->id) }}'" 
                                        class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-indigo-600 font-extrabold text-xs shadow-sm transition-all">
                                        ✏️ Edit
                                    </button>
                                    <button type="button" 
                                        @click="openPassModal = true; activeUser = '{{ $user->name }}'; resetUrl = '{{ route('admin.users.reset-password', $user->id) }}'" 
                                        class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-indigo-600 font-extrabold text-xs shadow-sm transition-all">
                                        🔑 Sandi
                                    </button>
                                @endif

                                {{-- Tombol Hapus (KHUSUS ADMIN) --}}
                                @role('Founder|Co-Founder|HR')
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('⚠️ Hapus akun {{ $user->name }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-all">🗑️</button>
                                    </form>
                                    @endif
                                @endrole
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    {{-- MODAL SECTION --}}
    
    {{-- Modal Create --}}
    <div x-cloak x-show="openCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div @click="openCreateModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-md"></div>
        <div class="relative bg-white p-8 rounded-[2.5rem] w-full max-w-lg z-[101]">
            <h3 class="text-xl font-black mb-6">Daftarkan Anggota Baru</h3>
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="text" name="name" required placeholder="Nama Lengkap" class="w-full p-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-bold outline-none focus:border-indigo-500">
                <input type="email" name="email" required placeholder="Email" class="w-full p-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-bold outline-none focus:border-indigo-500">
                <input type="password" name="password" required placeholder="Password Awal" class="w-full p-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-bold outline-none focus:border-indigo-500">
                <select name="role" class="w-full p-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-bold outline-none">
                    @foreach($roles as $role) <option value="{{ $role->name }}">{{ $role->name }}</option> @endforeach
                </select>
                <button type="submit" class="w-full bg-gray-900 text-white rounded-2xl py-4 font-black text-xs uppercase cursor-pointer">Daftarkan Anggota</button>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div x-cloak x-show="openEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div @click="openEditModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-md"></div>
        <div class="relative bg-white p-8 rounded-[2.5rem] w-full max-w-lg z-[101]">
            <h3 class="text-xl font-black mb-6">Edit Profil</h3>
            <form :action="updateUrl" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="text" name="name" x-model="editName" class="w-full p-4 rounded-2xl border bg-gray-50 text-sm font-bold outline-none">
                <input type="email" name="email" x-model="editEmail" class="w-full p-4 rounded-2xl border bg-gray-50 text-sm font-bold outline-none">
                
                @role('Founder|Co-Founder|HR')
                <select name="role" class="w-full p-4 rounded-2xl border bg-gray-50 text-sm font-bold outline-none">
                    @foreach($roles as $role) <option value="{{ $role->name }}">{{ $role->name }}</option> @endforeach
                </select>
                @endrole
                
                <button type="submit" class="w-full bg-gray-900 text-white rounded-2xl py-4 font-black text-xs uppercase cursor-pointer">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    {{-- Modal Reset Password --}}
    <div x-cloak x-show="openPassModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div @click="openPassModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-md"></div>
        <div class="relative bg-white p-8 rounded-[2.5rem] w-full max-w-md z-[101]">
            <h3 class="text-xl font-black mb-6">Ganti Sandi</h3>
            <form :action="resetUrl" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="password" name="password" required placeholder="Sandi Baru" class="w-full p-4 rounded-2xl bg-gray-50 border text-sm font-bold outline-none">
                <input type="password" name="password_confirmation" required placeholder="Konfirmasi Sandi" class="w-full p-4 rounded-2xl bg-gray-50 border text-sm font-bold outline-none">
                <button type="submit" class="w-full bg-gray-900 text-white rounded-2xl py-4 font-black text-xs uppercase cursor-pointer">Update Sandi</button>
            </form>
        </div>
    </div>
</body>
</html>