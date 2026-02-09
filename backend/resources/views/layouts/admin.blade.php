<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <nav>
        <!-- Sidebar/menu ici -->
    </nav>
    <main class="py-4">
        @yield('content')
    </main>
</body>
</html>
