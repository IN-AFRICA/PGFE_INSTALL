@extends('backend.auth.layouts.app')

@section('title', __('Connexion'))

@section('admin-content')
    <div class="flex flex-col space-y-8 fade-in" x-data="{ loading: false, showPassword: false }">

        {{-- Header spécifique au formulaire --}}
        <div class="flex flex-col space-y-2 text-center lg:text-left">
            {{-- Logo visible uniquement sur Mobile pour l'harmonie --}}
            <div class="flex items-center justify-center lg:hidden gap-3 mb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-600 shadow-sm">
                    <iconify-icon icon="lucide:graduation-cap" class="text-2xl text-white"></iconify-icon>
                </div>
                <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                {{ __('Heureux de vous revoir') }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Entrez vos identifiants pour accéder à l’administration') }}
            </p>
        </div>

        {{-- Formulaire --}}
        <form method="POST" action="{{ route('web.auth.login.submit') }}" class="space-y-6" @submit="loading = true">
            @csrf

            {{-- Erreurs style Shadcn Alert --}}
            @if (session('error') || $errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50/50 p-4 text-sm text-red-600 dark:border-red-900/30 dark:bg-red-900/20 dark:text-red-400 animate-in fade-in slide-in-from-top-1">
                    <div class="flex items-center gap-2 font-semibold mb-1">
                        <iconify-icon icon="lucide:alert-circle" class="text-lg"></iconify-icon>
                        <span>Erreur d'authentification</span>
                    </div>
                    <ul class="text-xs opacity-90 list-none space-y-0.5">
                        @if(session('error')) <li>{{ session('error') }}</li> @endif
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-4">
                {{-- Email --}}
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 ml-0.5">{{ __('Adresse e-mail') }}</label>
                    <div class="relative group">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 group-focus-within:text-violet-600 transition-colors">
                            <iconify-icon icon="lucide:mail" class="text-lg"></iconify-icon>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder="nom@ecole.com"
                               class="flex h-10 w-full rounded-md border border-zinc-200 bg-white pl-10 py-2 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 dark:border-zinc-800 dark:bg-zinc-950 dark:focus:ring-violet-500/10">
                    </div>
                </div>

                {{-- Password --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between ml-0.5">
                        <label for="password" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Mot de passe') }}</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-violet-600 hover:text-violet-500 transition-colors">Oublié ?</a>
                        @endif
                    </div>
                    <div class="relative group">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 group-focus-within:text-violet-600 transition-colors">
                            <iconify-icon icon="lucide:lock" class="text-lg"></iconify-icon>
                        </div>
                        <input id="password" name="password" type="password" :type="showPassword ? 'text' : 'password'" required placeholder="••••••••"
                               class="flex h-10 w-full rounded-md border border-zinc-200 bg-white pl-10 pr-10 py-2 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 dark:border-zinc-800 dark:bg-zinc-950 dark:focus:ring-violet-500/10">
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 transition-colors p-1" aria-label="Afficher le mot de passe">
                            <iconify-icon :icon="showPassword ? 'lucide:eye-off' : 'lucide:eye'" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center py-1">
                <input type="checkbox" id="remember" name="remember" class="h-4 w-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500/20 accent-violet-600 cursor-pointer">
                <label for="remember" class="ml-2 text-sm text-zinc-500 dark:text-zinc-400 cursor-pointer select-none">{{ __('Rester connecté') }}</label>
            </div>

            <button type="submit" :disabled="loading"
                    class="relative inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-zinc-50 shadow hover:bg-zinc-900/90 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-50/90 transition-all active:scale-[0.98] disabled:opacity-50 disabled:active:scale-100">
                <span x-show="!loading" class="flex items-center gap-2">
                    {{ __('Se connecter') }}
                    <iconify-icon icon="lucide:arrow-right" class="text-lg"></iconify-icon>
                </span>
                <iconify-icon x-show="loading" icon="lucide:loader-2" class="animate-spin text-xl" x-cloak></iconify-icon>
            </button>
        </form>

        <footer class="text-center lg:text-left pt-4 border-t border-zinc-100 dark:border-zinc-900">
            <p class="text-[11px] text-zinc-400 dark:text-zinc-500 tracking-wide uppercase font-medium">
                &copy; {{ date('Y') }} {{ config('app.name') }} — {{ __('Portail Sécurisé') }}
            </p>
        </footer>
    </div>
@endsection
