<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - {{ $user->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; } 
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F4F7F6] min-h-screen flex items-center justify-center p-6">

    <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-gray-100 -z-10"></div>

    <div class="w-full max-w-lg bg-white p-8 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-gray-100">
        
        <div class="mb-8">
            <h2 class="text-2xl font-black text-gray-900 tracking-tight">Edit Profil</h2>
            <p class="text-sm text-gray-500 font-medium">Sedang menyunting data akun: <span class="font-bold text-indigo-600">{{ $user->name }}</span></p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-semibold text-gray-900 focus:bg-white focus:border-indigo-500 outline-none transition-all">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-semibold text-gray-900 focus:bg-white focus:border-indigo-500 outline-none transition-all">
            </div>

            @role('Founder|Co-Founder|HR')
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Hak Akses Role</label>
                <select name="role" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm font-extrabold text-gray-900 focus:bg-white focus:border-indigo-500 outline-none transition-all cursor-pointer">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endrole

            <div class="pt-4 flex gap-3">
                <a href="{{ route('admin.users.index') }}" class="flex-1 py-4 text-center rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-xs uppercase cursor-pointer transition-all">Batal</a>
                <button type="submit" class="flex-1 py-4 rounded-2xl bg-gray-900 hover:bg-black text-white font-black text-xs uppercase cursor-pointer transition-all shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>

</body>
</html>