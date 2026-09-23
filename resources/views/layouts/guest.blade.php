<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <title>@yield('title', 'Control ATS')</title>
</head>
<body class="grid min-h-svh place-items-center p-5">
    <main class="w-full max-w-sm">
        <div class="mb-9 flex items-center justify-center gap-2.5 text-[15px] font-semibold tracking-tight">
            <span class="grid h-9 w-9 place-items-center rounded-xl border border-zinc-200 bg-white" aria-hidden="true">
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

        <section class="rounded-2xl border border-zinc-200 bg-white p-7 shadow-[0_4px_20px_rgb(0_0_0_/_3%)]">
            @yield('content')
        </section>

        <p class="mt-6 text-center text-xs text-zinc-500">
            Sistema de control de registros ATS y EPP
        </p>
    </main>
</body>
</html>
