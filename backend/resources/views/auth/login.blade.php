@extends('backend.auth.layouts.app')

@section('title')
    {{ __('Connexion') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
    <div>
        <div class="mb-6 sm:mb-8 text-center sm:text-left">
            <h1 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90 sm:text-3xl">
                {{ __('Heureux de vous revoir') }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-300">
                {{ __('Connectez-vous pour accéder à l’interface d’administration.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('web.auth.login.submit') }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            @if (session('error'))
                <div class="rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-700/20 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-700/20 dark:text-red-200">
                    <ul class="list-disc ml-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="email" class="form-label">E-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="form-control"
                    placeholder="nom@domaine.com"
                    autocomplete="username"
                >
            </div>

            <x-inputs.password
                name="password"
                label="{{ __('Mot de passe') }}"
                placeholder="{{ __('Votre mot de passe') }}"
                required
            />

            <div class="flex items-center justify-between">
                <label for="remember" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800"
                    >
                    <span>Se souvenir de moi</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">
                        {{ __('Mot de passe oublié ?') }}
                    </a>
                @endif
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="btn-primary w-full flex items-center justify-center gap-2"
                    :disabled="loading"
                >
                    <span x-text="loading ? '' : '{{ __('Se connecter') }}'">{{ __('Se connecter') }}</span>
                    <iconify-icon
                        :icon="loading ? 'lucide:loader-circle' : 'lucide:log-in'"
                        :class="{ 'animate-spin': loading }"
                    ></iconify-icon>
                </button>
            </div>
        </form>
    </div>
@endsection
