<footer class="mt-auto py-6 border-t border-zinc-100 bg-white/50">
    <div class="px-8 flex flex-col md:flex-row items-center justify-between gap-4">
        
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] font-bold uppercase tracking-widest text-zinc-400">
            <span>&copy; {{ date('Y') }} — {{ config('app.name', 'PGFE') }}</span>
            <span class="h-1 w-1 rounded-full bg-zinc-200"></span>
            <span class="font-mono text-[9px] opacity-70">
                v{{ Illuminate\Foundation\Application::VERSION }} (PHP {{ PHP_VERSION }})
            </span>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-tighter italic">Réalisé par</span>
                <span class="text-xs font-black text-zinc-900 tracking-tight">Rooney Kalumba</span>
            </div>

            <div class="flex items-center gap-3 border-l border-zinc-100 pl-6">
                <a href="mailto:rooneykalumba610@gmail.com" 
                   class="text-zinc-400 hover:text-zinc-900 transition-colors" 
                   title="{{ __('Email Me') }}">
                    <iconify-icon icon="lucide:mail" width="16"></iconify-icon>
                </a>
                <a href="https://github.com/IN-AFRICA" 
                   target="_blank" 
                   class="text-zinc-400 hover:text-zinc-900 transition-colors" 
                   title="{{ __('GitHub Profile') }}">
                    <iconify-icon icon="lucide:github" width="16"></iconify-icon>
                </a>
            </div>
        </div>

    </div>
</footer>