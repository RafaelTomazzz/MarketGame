<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MarketGame')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="mg-auth-page @yield('layout-class') d-flex align-items-center justify-content-center min-vh-100 p-3">
        <section class="card mg-auth-card border-0 shadow">
            <div class="card-body p-4 p-md-5">
                @yield('content')
            </div>
        </section>
    </main>
</body>
</html>
