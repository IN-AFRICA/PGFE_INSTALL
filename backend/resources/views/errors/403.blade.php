@extends('backend.auth.layouts.app')

@section('title', '403 - ' . __('Accès Refusé'))

@section('admin-content')
    <div class="flex flex-col items-center justify-center text-center space-y-6 fade-in">

        {{-- Icône d'alerte stylisée --}}
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-red-50 text-red-600 shadow-sm border border-red-100 mb-2">
            <iconify-icon icon="lucide:shield-off" class="text-4xl"></iconify-icon>
        </div>

        {{-- Texte d'erreur --}}
        <div class="space-y-2">
            <h1 class="text-4xl font-black tracking-tighter text-zinc-900">403</h1>
            <h2 class="text-xl font-bold text-zinc-800">{{ __('Accès Refusé') }}</h2>
            <p class="text-sm text-zinc-500 max-w-[280px] mx-auto leading-relaxed">
                {{ __('Désolé, vous n\'avez pas les autorisations nécessaires pour accéder à cette ressource.') }}
            </p>
        </div>

        {{-- Message d'exception (si présent) --}}
        @if($exception->getMessage())
            <div class="w-full rounded-lg border border-red-100 bg-red-50/50 p-3 text-xs text-red-700 font-medium flex items-center justify-center gap-2">
                <iconify-icon icon="lucide:info" class="text-base"></iconify-icon>
                <span>{{ $exception->getMessage() }}</span>
            </div>
        @endif

        {{-- Actions / Liens --}}
        <div class="pt-4 w-full space-y-3">
            <a href="{{ url()->previous() }}"
               class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-zinc-50 shadow transition-all hover:bg-zinc-800 active:scale-[0.98]">
                <iconify-icon icon="lucide:arrow-left" class="text-lg"></iconify-icon>
                {{ __('Retourner en arrière') }}
            </a>
        </div>

        <footer class="pt-6 border-t border-zinc-100 w-full">
            <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold">
                {{ config('app.name') }} Security System
            </p>
        </footer>
    </div>
@endsection
