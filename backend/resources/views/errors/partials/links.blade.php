<a href="{{ url()->previous() }}" 
   class="inline-flex items-center justify-center rounded-md border border-zinc-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-widest text-zinc-600 shadow-sm transition-all hover:bg-zinc-50 hover:text-zinc-900">
    <iconify-icon icon="lucide:arrow-left" class="mr-2" width="14"></iconify-icon>
    Retour
</a>


<form method="POST" action="{{ route('web.auth.logout') }}" class="inline">
    @csrf
    <button type="submit" 
            class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-xs font-bold uppercase tracking-widest text-zinc-50 shadow-md shadow-zinc-200 transition-all hover:bg-black">
        Se reconnecter
        <iconify-icon icon="lucide:log-in" class="ml-2" width="14"></iconify-icon>
    </button>
</form>