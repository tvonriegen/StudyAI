<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'StudyAI')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">

<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">

        <a href="{{ route('home') }}"
           class="text-2xl font-bold text-indigo-600">
            StudyAI
        </a>

        <span class="text-sm text-slate-500">
            Tu compañero inteligente de estudio
        </span>

    </div>
</header>

<main>
    @yield('content')
</main>

</body>
</html>