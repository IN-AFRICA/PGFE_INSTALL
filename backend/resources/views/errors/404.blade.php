@extends('backend.auth.layouts.app')

@section('title')
    404 - Page Introuvable - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="relative z-1 flex min-h-screen flex-col items-center justify-center overflow-hidden bg-white p-6">
    <div class="mx-auto w-full max-w-[520px] text-center">
        
        <div class="mb-6 flex justify-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-zinc-50 border border-zinc-100 shadow-sm">
                <iconify-icon icon="lucide:map-pin-off" class="text-zinc-400" width="40"></iconify-icon>
            </div>
        </div>

        <h1 class="text-[140px] font-bold leading-none tracking-tighter text-zinc-100 select-none">
            404
        </h1>
        
        <div class="-mt-12 relative z-10">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900 uppercase">
                Page Introuvable
            </h2>
            <p class="mt-4 text-sm font-medium text-zinc-500 leading-relaxed mx-auto max-w-[380px]">
                Désolé, la ressource que vous recherchez semble avoir été déplacée, supprimée ou n'a jamais existé.
            </p>
        </div>

        <div class="mt-12 flex flex-col items-center gap-4 border-t border-zinc-100 pt-10">
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">Où souhaitez-vous aller ?</span>
            
            <div class="flex flex-wrap justify-center gap-3">
                @include('errors.partials.links')
            </div>
        </div>

        <p class="mt-16 text-[10px] font-medium text-zinc-300 uppercase tracking-[0.3em]">
            SYSTÈME D'INFORMATION — {{ config('app.name') }}
        </p>
    </div>
</div>
@endsection