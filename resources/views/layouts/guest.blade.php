<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('assets/css/guest.css') }}">
    <title>@yield('title', 'Control ATS')</title>
</head>
<body>
    <main class="page">
        <div class="auth-shell">
            <div class="brand">
                <span class="brand-icon" aria-hidden="true">
                    <svg width="21" height="21" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.7"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <path d="M12 3 4.5 6v5.5c0 4.2 3 7.4 7.5 9.5
                                 4.5-2.1 7.5-5.3 7.5-9.5V6L12 3Z"/>
                        <path d="m8.5 12 2.3 2.3 4.7-4.7"/>
                    </svg>
                </span>
                Control ATS
            </div>

            <section class="card">
                @yield('content')
            </section>

            <p class="footer">
                Sistema de control de registros ATS y EPP
            </p>
        </div>
    </main>
</body>
</html>