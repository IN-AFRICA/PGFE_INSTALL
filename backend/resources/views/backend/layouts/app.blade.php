<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-screen bg-gray-50 text-gray-800 antialiased dark:bg-gray-900 dark:text-gray-100">
    <!-- Barre supérieure minimale -->
    <header class="w-full bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="w-full px-4 sm:px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-base font-semibold text-violet-700 dark:text-violet-400">{{ config('app.name') }}</span>
                @php
                    $selSchoolId = session('selected_school_id');
                    $selClassId = session('selected_classroom_id');
                    $selSchool = $selSchoolId ? \App\Models\School::find($selSchoolId) : null;
                    $selClass = $selClassId ? \App\Models\Classroom::find($selClassId) : null;
                @endphp
                @if($selSchool)
                    <span class="inline-flex items-center gap-1 text-xs rounded-full bg-blue-100 text-blue-700 px-2 py-1 dark:bg-blue-700/20 dark:text-blue-300" title="École sélectionnée">
                        <iconify-icon icon="mdi:school" width="14" height="14"></iconify-icon>
                        {{ $selSchool->name }}
                    </span>
                @endif
                @if($selClass)
                    <span class="inline-flex items-center gap-1 text-xs rounded-full bg-emerald-100 text-emerald-700 px-2 py-1 dark:bg-emerald-700/20 dark:text-emerald-300" title="Classe sélectionnée">
                        <iconify-icon icon="lucide:layers" width="14" height="14"></iconify-icon>
                        {{ $selClass->name }}
                    </span>
                @endif
                @yield('header-left')
            </div>
            <div class="flex items-center gap-4">
                @auth
                @php
                $raw = trim(auth()->user()->name ?? auth()->user()->email ?? 'Utilisateur');
                $token = $raw !== '' ? preg_split('/\s+/', $raw)[0] : 'Utilisateur';
                $ini = strtoupper(mb_substr($token, 0, 2));
                @endphp
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-violet-600 text-white flex items-center justify-center text-xs font-semibold">{{ $ini }}</div>
                    <span class="text-sm font-medium truncate max-w-[120px]">{{ $token }}</span>
                    <form method="POST" action="{{ route('web.auth.logout') }}" class="ml-2">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 font-medium">Déconnexion</button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <div class="flex w-full min-h-[calc(100vh-3.5rem)] bg-gray-50 dark:bg-gray-950 p-4 gap-6">
        <!-- Floating Sidebar -->
        @yield('main-sidebar')
        @hasSection('main-sidebar')
        @else
        <aside class="hidden lg:block w-64 shrink-0">
            <div class="sticky top-6 h-[calc(100vh-5rem)] bg-white dark:bg-gray-900 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800 flex flex-col overflow-hidden transition-all duration-300">
                <!-- Logo/brand -->
                <div class="p-6 border-b border-gray-50 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-violet-500/30">
                            <iconify-icon icon="lucide:zap" width="24"></iconify-icon>
                        </div>
                        <span class="text-lg font-black tracking-tighter text-gray-800 dark:text-white uppercase">{{ config('app.name') }}</span>
                    </div>
                </div>

                <!-- Menu accordéon scrollable -->
                <div class="flex-1 overflow-y-auto py-6 custom-scrollbar">
                    @includeIf('backend.layouts.partials.sidebar.menu')
                </div>

                <!-- Footer Sidebar -->
                <div class="p-4 border-t border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-3 p-2 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
                        <div class="h-8 w-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                            {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-black text-gray-400 uppercase leading-none">Connecté en tant que</p>
                            <p class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate">{{ explode(' ', auth()->user()->name)[0] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        @endif

        <!-- Contenu principal -->
        <main class="flex-1 min-w-0">
            <div class="max-w-7xl mx-auto space-y-6">
                @if(session('success'))
                    <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 px-6 py-4 text-sm text-emerald-800 border border-emerald-100 shadow-sm animate-in fade-in slide-in-from-top-2">
                        <iconify-icon icon="lucide:check-circle" width="20"></iconify-icon>
                        <span class="font-bold">{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="flex items-center gap-3 rounded-2xl bg-rose-50 px-6 py-4 text-sm text-rose-800 border border-rose-100 shadow-sm animate-in fade-in slide-in-from-top-2">
                        <iconify-icon icon="lucide:alert-circle" width="20"></iconify-icon>
                        <span class="font-bold">{{ session('error') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="rounded-2xl bg-rose-50 px-6 py-4 text-sm text-rose-800 border border-rose-100 shadow-sm animate-in fade-in slide-in-from-top-2">
                        <div class="flex items-center gap-3 mb-2">
                            <iconify-icon icon="lucide:x-circle" width="20"></iconify-icon>
                            <span class="font-black uppercase text-xs tracking-widest">Erreurs de validation</span>
                        </div>
                        <ul class="list-disc ml-8 space-y-0.5 font-medium opacity-80 uppercase text-[10px]">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @hasSection('admin-content')
                   @yield('admin-content')
                @else
                   {{ $slot ?? '' }}
                @endif
            </div>
            
            <!-- Pied de page -->
            <footer class="mt-12 py-8 text-center">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
                    &copy; {{ date('Y') }} {{ config('app.name') }} &bull; Système de Supervision Mondiale
                </p>
            </footer>
        </main>
    </div>

    @stack('scripts')
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; }
    </style>
</body>

</html>
