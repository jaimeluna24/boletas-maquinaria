<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-init="document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') ?? 'light')">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.documentElement.setAttribute(
            'data-theme',
            localStorage.getItem('theme') ?? 'light'
        );
    </script>
    <style>
        .logo-dark {
            display: none;
            width: 130px;
        }

        [data-theme="dark"] .logo-light {
            display: none;
        }

        [data-theme="dark"] .logo-dark {
            display: block;
        }
    </style>
    @livewireStyles
</head>

<body class="bg-base-200/0 text-base-content">
    <nav class="navbar rounded-box justify-between gap-4 shadow-base-300/20 shadow-sm">
        <div class="navbar-start">
            <div class="items-center">
                <img src="{{ asset('images/logo_achsa_light.jpg') }}" alt="Logo" class="logo-light" style="width: 130px">

                <img src="{{ asset('images/logo_achsa_dark.png') }}" alt="Logo" class="logo-dark">


            </div>
        </div>
        <div id="dropdown-navbar-collapse"
            class="md:navbar-center overflow-hidden transition-[height] duration-300 max-md:w-full z-20">
            <ul class="menu md:menu-horizontal gap-2 p-0 text-base max-md:mt-2">
                {{-- <li><a href="/dashboard">
                            <span class="icon-[boxicons--chart-line] size-4.5 shrink-0"></span>
                    Dashboard</a></li> --}}
                {{-- <li class="dropdown relative inline-flex [--auto-close:inside] [--offset:9] [--placement:bottom-end]">
                    <button id="dropdown-nav" type="button"
                        class="dropdown-toggle dropdown-open:bg-base-content/10 dropdown-open:text-base-content"
                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                            <span class="icon-[quill--list] size-4.5 shrink-0"></span>
                        Solicitudes
                        <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden" role="menu"
                        aria-orientation="vertical" aria-labelledby="dropdown-nav">
                        <li><a class="dropdown-item" href="/mis-solicitudes">
                            <span class="icon-[material-symbols--list] size-4.5 shrink-0"></span>
                            Mis Solicitudes</a></li>
                        <li><a class="dropdown-item" href="/crear-solicitud">
                            <span class="icon-[fluent--form-new-20-regular] size-4.5 shrink-0"></span>
                            Solicitar Maquinaria</a></li>
                        <li><a class="dropdown-item" href="/solicitudes">
                            <span class="icon-[lsicon--management-outline] size-4.5 shrink-0"></span>
                            Gestionar Solicitudes</a></li>
                    </ul>
                </li> --}}
                <li class="dropdown relative inline-flex [--auto-close:inside] [--offset:9] [--placement:bottom-end]">
                    <button id="dropdown-nav" type="button"
                        class="dropdown-toggle dropdown-open:bg-base-content/10 dropdown-open:text-base-content"
                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        <span class="icon-[hugeicons--list-end] size-4.5 shrink-0"></span>
                        Distribución de Maquinaria
                        <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden" role="menu"
                        aria-orientation="vertical" aria-labelledby="dropdown-nav">
                        <li><a class="dropdown-item" href="/distribuciones-diarias">
                                <span class="icon-[hugeicons--task-daily-01] size-4.5 shrink-0"></span>
                                Distribución Diaria
                            </a></li>
                        <li><a class="dropdown-item" href="/distribuciones-historico">
                                <span class="icon-[solar--calendar-line-duotone] size-4.5 shrink-0"></span>
                                Distribución General</a></li>
                        {{-- <hr class="border-base-content/25 -mx-2" />
                        <li><a class="dropdown-item" href="#">Figma designs</a></li> --}}
                    </ul>
                </li>
                <li class="dropdown relative inline-flex [--auto-close:inside] [--offset:9] [--placement:bottom-end]">
                    <button id="dropdown-nav" type="button"
                        class="dropdown-toggle dropdown-open:bg-base-content/10 dropdown-open:text-base-content"
                        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                        <span class="icon-[oui--nav-administration] size-4.5 shrink-0"></span>
                        Administración
                        <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden" role="menu"
                        aria-orientation="vertical" aria-labelledby="dropdown-nav">
                        <li><a class="dropdown-item" href="/maquinaria">
                                <span class="icon-[material-symbols--agriculture-outline] size-4.5 shrink-0"></span>
                                Maquinaria
                            </a></li>
                        <li><a class="dropdown-item" href="/implementos">
                                <span class="icon-[carbon--partition-repartition] size-4.5 shrink-0"></span>
                                Implementos
                            </a></li>
                        <li><a class="dropdown-item" href="/operadores">
                                <span class="icon-[healthicons--truck-driver] size-4.5 shrink-0"></span>
                                Operadores
                            </a></li>
                        {{-- <li><a class="dropdown-item" href="/actividades">
                            <span class="icon-[fluent--broad-activity-feed-16-regular] size-4.5 shrink-0"></span>
                            Actividades
                        </a></li> --}}
                        <li><a class="dropdown-item" href="/tiempo-perdido">
                                <span class="icon-[mdi--tool-time] size-4.5 shrink-0"></span>
                                Tiempo Perdido
                            </a></li>
                        <li><a class="dropdown-item" href="/usuarios">
                                <span class="icon-[garden--user-list-stroke-12] size-4.5 shrink-0"></span>
                                Usuarios
                            </a></li>
                        {{-- <hr class="border-base-content/25 -mx-2" />
                        <li><a class="dropdown-item" href="#">Figma designs</a></li> --}}
                    </ul>
                </li>
            </ul>
        </div>
        <div class="navbar-end items-center gap-4">
            @include('components.theme-toggle')

            <livewire:notificaciones />

            <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end] z-20">
                <button id="dropdown-scrollable" type="button" class="dropdown-toggle flex items-center"
                    aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                    <div class="avatar">
                        <div class="size-9.5 rounded-full">
                            <span class="icon-[basil--user-solid] size-8"></span>
                        </div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu"
                    aria-orientation="vertical" aria-labelledby="dropdown-avatar">
                    <li class="dropdown-header gap-2">
                        <div class="avatar">
                            <div class="w-10 rounded-full">
                                <span class="icon-[basil--user-solid] size-10"></span>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-base-content text-base font-semibold">{{ auth()->user()->nombre_completo }}
                            </h6>
                            <small
                                class="text-base-content/50">{{ auth()->user()->roles->first()?->name ?? 'Sin Rol' }}</small>
                        </div>
                    </li>
                    {{-- <li>
                        <a class="dropdown-item" href="#">
                            <span class="icon-[tabler--user]"></span>
                            My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="icon-[tabler--settings]"></span>
                            Settings
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="icon-[tabler--receipt-rupee]"></span>
                            Billing
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="icon-[tabler--help-triangle]"></span>
                            FAQs
                        </a>
                    </li> --}}
                    <li class="dropdown-footer gap-2">
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="btn btn-error btn-soft btn-block">
                                <span class="icon-[tabler--logout]"></span>
                                Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    {{ $slot }}

    <x-alert />
    @livewireScripts
    <script src="../node_modules/flyonui/flyonui.js"></script>
</body>

</html>
