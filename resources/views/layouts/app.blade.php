<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>@yield('title', 'Inicio') · Control ATS</title>
</head>
<body>
    <div class="grid min-h-screen grid-cols-1 lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside class="flex flex-col border-b border-zinc-200 bg-white p-5 lg:border-r lg:border-b-0 lg:p-7">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2 text-[17px] font-bold tracking-tight">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-zinc-900 text-white" aria-hidden="true">✓</span>
                Control ATS
            </a>

            <p class="mt-9 px-3 text-[11px] font-semibold uppercase tracking-widest text-zinc-500">
                Espacio de trabajo
            </p>

            <nav class="mt-3 flex flex-col gap-1" aria-label="Navegación principal">
                @can('usuarios.ver')
                    <a href="{{ route('users.index') }}"
                       class="nav-link {{ request()->routeIs('users.*') ? 'nav-link-active' : '' }}"
                       @if (request()->routeIs('users.*')) aria-current="page" @endif>
                        Usuarios
                    </a>
                @endcan

                @can('maquinas.ver')
                    <a href="{{ route('machines.index') }}"
                       class="nav-link {{ request()->routeIs('machines.*') ? 'nav-link-active' : '' }}"
                       @if (request()->routeIs('machines.*')) aria-current="page" @endif>
                        Máquinas
                    </a>
                @endcan
            </nav>

            <div class="mt-auto px-1 pt-9">
                <p class="break-words text-[13px] font-semibold">
                    {{ auth()->user()->name }}
                    {{ auth()->user()->last_name }}
                </p>

                <p class="mb-4 text-xs text-zinc-500">
                    {{ auth()->user()->roles->first()?->name ?? 'Sin rol asignado' }}
                </p>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-logout" type="submit">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="flex h-14 items-center border-b border-zinc-200 bg-white px-5 text-sm font-medium lg:h-[72px] lg:px-10">
                @yield('title', 'Inicio')
            </header>

            <main class="mx-auto w-full max-w-6xl p-5 lg:p-10">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
