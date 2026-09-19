<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <title>@yield('title', 'Inicio') · Control ATS</title>

</head>
<body data-auth-id="{{ auth()->id() }}">
    <div class="app">
        <aside class="sidebar">
            <a href="{{ route('dashboard') }}" class="brand">
                <span class="brand-mark" aria-hidden="true">✓</span>
                Control ATS
            </a>

            <p class="nav-label">Espacio de trabajo</p>

            <nav aria-label="Navegación principal">
                @can('usuarios.ver')
                    <a href="{{ route('users.index') }}"
                    class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                    @if(request()->routeIs('users.*')) aria-current="page" @endif>
                        Usuarios
                    </a>
                @endcan
            </nav>

            <div class="account">
                <p class="account-name">
                    {{ auth()->user()->name }}
                    {{ auth()->user()->last_name }}
                </p>

                <p class="account-role muted">
                    {{ auth()->user()->role->name }}
                </p>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout" type="submit">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="workspace">
            <header class="topbar">
                @yield('title', 'Inicio')
            </header>

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>