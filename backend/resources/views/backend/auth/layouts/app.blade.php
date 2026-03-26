<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'PGFE'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/js/app.js', 'resources/css/app.css'])

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f4f5; } /* Zinc-100 */
        [x-cloak] { display: none !important; }
        .fade-in { animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @yield('styles')
</head>

<body class="bg-zinc-100 antialiased text-zinc-950">

<div class="flex min-h-screen">
    <main class="flex w-full items-center justify-center lg:w-1/2 p-6 md:p-12 fade-in">
        <div class="w-full max-w-[420px]">
            {{-- La carte de connexion : Blanche, nette, avec une ombre subtile --}}
            <div class="bg-white p-8 md:p-10 rounded-xl border border-zinc-200 shadow-xl shadow-zinc-200/50">
                @yield('admin-content')
            </div>
        </div>
    </main>

    <section class="relative hidden w-1/2 lg:flex items-center justify-center overflow-hidden bg-slate-900 border-l border-slate-800">

        <div class="absolute inset-0 z-0">
            <div class="absolute top-0 right-0 h-[500px] w-[500px] rounded-full bg-violet-600/20 blur-[120px]"></div>
            <div class="absolute bottom-0 left-0 h-[400px] w-[400px] rounded-full bg-blue-600/20 blur-[100px]"></div>
        </div>

        <div class="relative z-10 w-full max-w-md p-10 text-center space-y-8">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-800 border border-slate-700 shadow-2xl">
                <iconify-icon icon="lucide:graduation-cap" class="text-3xl text-violet-400"></iconify-icon>
            </div>

            <div class="space-y-3">
                <h2 class="text-4xl font-bold tracking-tight text-white">
                    {{ config('app.name') }}
                </h2>
                <p class="text-lg font-medium text-violet-300/90">
                    L'excellence académique simplifiée.
                </p>
                <div class="h-1 w-10 bg-violet-500/50 mx-auto rounded-full"></div>
                <p class="text-sm text-slate-400 leading-relaxed max-w-[300px] mx-auto">
                    Plateforme de gestion intégrée pour les établissements scolaires d'élite.
                </p>
            </div>

            <div class="flex flex-col gap-3 pt-4">
                <div class="flex items-center justify-between rounded-lg border border-slate-700 bg-slate-800/50 p-4 backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <iconify-icon icon="lucide:shield-check" class="text-violet-400 text-lg"></iconify-icon>
                        <span class="text-xs font-semibold text-slate-200">Accès Sécurisé </span>
                    </div>
                    <span class="h-2 w-2 rounded-full bg-violet-400 shadow-[0_0_8px_rgba(167,139,250,0.6)]"></span>
                </div>

                <div class="flex items-center justify-between rounded-lg border border-slate-700 bg-slate-800/50 p-4 backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <iconify-icon icon="lucide:zap" class="text-amber-400 text-lg"></iconify-icon>
                        <span class="text-xs font-semibold text-slate-200">Haute Disponibilité</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-400 uppercase">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute inset-0 opacity-[0.15] pointer-events-none"
             style="background-image: linear-gradient(#334155 1px, transparent 1px), linear-gradient(90deg, #334155 1px, transparent 1px); background-size: 40px 40px;"></div>
    </section>
</div>

@stack('scripts')
</body>
</html>
