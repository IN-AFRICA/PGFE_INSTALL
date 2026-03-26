@extends('backend.auth.layouts.app')

@section('title')
    500 - Erreur Interne du Serveur - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="relative z-1 flex min-h-screen flex-col items-center justify-center overflow-hidden bg-white p-6">
    <div class="mx-auto w-full max-w-[480px] text-center">
        
        <div class="mb-6 flex justify-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-zinc-50 border border-zinc-100 shadow-sm">
                <iconify-icon icon="lucide:alert-octagon" class="text-zinc-400" width="40"></iconify-icon>
            </div>
        </div>

        <h1 class="text-[120px] font-bold leading-none tracking-tighter text-zinc-100 select-none">
            500
        </h1>
        
        <div class="-mt-10 relative z-10">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900 uppercase">
                Erreur Interne
            </h2>
            <p class="mt-3 text-sm font-medium text-zinc-500 leading-relaxed">
                Une anomalie imprévue est survenue sur nos serveurs.<br> 
                Nos équipes techniques ont été alertées.
            </p>
        </div>

        <div class="mt-10 flex flex-col items-center gap-4 border-t border-zinc-100 pt-10">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400 mb-2">Options de récupération</p>
            
            <div class="flex flex-wrap justify-center gap-3">
                @include('errors.partials.links')
            </div>
        </div>

        <p class="mt-12 text-[10px] font-medium text-zinc-300 uppercase tracking-widest">
            &copy; {{ date('Y') }} — {{ config('app.name') }} System
        </p>
    </div>
</div>
@endsection