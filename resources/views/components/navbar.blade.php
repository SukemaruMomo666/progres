<nav class="bg-white/70 backdrop-blur-2xl border-b border-white/60 px-8 py-5 flex justify-between items-center sticky top-0 z-50 shadow-[0_4px_30px_rgba(0,0,0,0.03)]">
    
    <div class="flex items-center gap-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-4 group">
            <div class="w-10 h-10 bg-gray-900 rounded-[0.8rem] flex items-center justify-center shadow-sm group-hover:bg-black group-hover:shadow-md transition-all duration-300">
                <span class="text-white font-extrabold text-xl leading-none tracking-tighter mt-[-2px]">X</span>
            </div>
            <div class="hidden sm:block">
                <h1 class="text-xl font-extrabold tracking-tight text-gray-900 group-hover:text-black transition-colors leading-none">XGrow</h1>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1.5">Studio Workspace</p>
            </div>
        </a>
        
        <div class="h-8 w-px bg-gray-200 mx-2 hidden md:block"></div>
        
        <span class="hidden md:flex items-center gap-2 bg-gray-50 border border-gray-200/80 text-gray-600 text-[10px] px-3 py-1.5 rounded-lg font-extrabold uppercase tracking-widest shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            {{ $title ?? 'Overview' }}
        </span>
    </div>

    <div class="flex items-center gap-5">
        <div class="flex items-center gap-3 text-right">
            <div class="hidden md:block mt-1">
                <p class="text-sm font-extrabold text-gray-900 leading-none">{{ Auth::user()->name ?? 'Guest' }}</p>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1.5">{{ Auth::user()->roles->pluck('name')->first() ?? 'Staff' }}</p>
            </div>
            
            <div class="relative group cursor-pointer ml-1">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-gray-100 to-gray-200 border-2 border-white shadow-sm flex items-center justify-center font-extrabold text-gray-700 ring-2 ring-transparent group-hover:ring-gray-200 group-hover:-translate-y-0.5 transition-all duration-300">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
            </div>
        </div>

        <div class="h-8 w-px bg-gray-200 mx-1"></div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-11 h-11 rounded-2xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 shadow-sm hover:shadow-md transition-all duration-300 group" title="Sign Out">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
        </form>
    </div>
</nav>