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

<body class="min-h-screen bg-[#f5f3ff] text-gray-800 antialiased dark:bg-gray-950 dark:text-gray-100">
    <main class="w-full min-h-screen">
        <div class="w-full px-4 sm:px-10 py-8">
            @if(session('success'))
                    <div class="mb-4 flex items-center gap-3 rounded-2xl bg-emerald-50/80 px-6 py-4 text-sm text-emerald-800 border border-emerald-100 shadow-sm">
                        <iconify-icon icon="lucide:check-circle" width="20"></iconify-icon>
                        <span class="font-bold">{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 flex items-center gap-3 rounded-2xl bg-rose-50/80 px-6 py-4 text-sm text-rose-800 border border-rose-100 shadow-sm">
                        <iconify-icon icon="lucide:alert-circle" width="20"></iconify-icon>
                        <span class="font-bold">{{ session('error') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 rounded-2xl bg-rose-50 px-6 py-4 text-sm text-rose-800 border border-rose-100 shadow-sm">
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
    </main>

    @stack('scripts')
</body>

</html>
