<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#195a36">

    <title>@yield('title', 'Painel') — SIGAD</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">
    <div data-sidebar-overlay data-sidebar-close class="fixed inset-0 z-40 hidden bg-slate-950/45 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

    <aside data-app-sidebar class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white shadow-sidebar transition-transform duration-200 lg:translate-x-0 lg:shadow-none">
        <div class="flex min-h-24 items-center justify-between bg-brand-800 px-5">
            <x-application-logo inverted/>

            <button data-sidebar-close type="button" class="grid size-10 place-items-center rounded-xl text-white/80 transition hover:bg-white/10 hover:text-white lg:hidden" aria-label="Fechar menu">
                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <nav class="flex flex-1 flex-col overflow-y-auto px-4 py-6" aria-label="Navegação principal">
            <p class="px-3 text-[0.65rem] font-extrabold uppercase tracking-[0.18em] text-slate-400">Menu principal</p>

            <div class="mt-3 grid gap-1.5">
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="m3 11 9-8 9 8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5.5 9.5V21h13V9.5M9.5 21v-6h5v6" stroke-linejoin="round"/>
                        </svg>
                    </x-slot:icon>
                    Início
                </x-sidebar-link>

                <x-sidebar-link :href="route('plans.index')" :active="request()->routeIs('plans.*')">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H10l2 2h5.5A2.5 2.5 0 0 1 20 7.5v10a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-12Z" stroke-linejoin="round"/>
                        </svg>
                    </x-slot:icon>
                    Planos de Trabalho
                </x-sidebar-link>

                <x-sidebar-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 7.5 10.5 3H20v9.5L13.5 19 4 9.5v-2Z" stroke-linejoin="round"/>
                            <circle cx="15.5" cy="7.5" r="1.25"/>
                        </svg>
                    </x-slot:icon>
                    Categorias
                </x-sidebar-link>

                <x-sidebar-link :href="route('activities.overview')" :active="request()->routeIs('activities.*', 'plans.activities.*')">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M8 4h8M9 2h6v4H9zM6 4H5a2 2 0 0 0-2 2v15h18V6a2 2 0 0 0-2-2h-1" stroke-linejoin="round"/>
                            <path d="m7 13 3 3 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </x-slot:icon>
                    Atividades
                </x-sidebar-link>

                <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 21V10m5 11V4m6 17v-7m5 7V7" stroke-linecap="round"/>
                            <path d="M2 21h20" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                    Relatórios
                </x-sidebar-link>

                @if (auth()->user()->isAdministrator())
                    <div class="my-2 border-t border-slate-100"></div>

                    <x-sidebar-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                        <x-slot:icon>
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M12 3 4.5 6.5v5.2c0 4.4 3 8.2 7.5 9.3 4.5-1.1 7.5-4.9 7.5-9.3V6.5L12 3Z" stroke-linejoin="round"/>
                                <path d="M9 12h6M12 9v6" stroke-linecap="round"/>
                            </svg>
                        </x-slot:icon>
                        Usuários
                    </x-sidebar-link>
                @endif
            </div>

            <div class="mt-auto border-t border-slate-100 pt-5">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-100 font-bold text-brand-800">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold text-slate-800">{{ auth()->user()->name }}</span>
                        <span class="block truncate text-xs text-slate-500">{{ auth()->user()->email }}</span>
                    </span>
                </div>
            </div>
        </nav>
    </aside>

    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="flex min-h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button data-sidebar-open type="button" class="grid size-11 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 lg:hidden" aria-label="Abrir menu">
                        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                        </svg>
                    </button>

                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-extrabold tracking-tight text-slate-950 sm:text-xl">@yield('page_title', 'Painel')</h1>
                        <p class="hidden truncate text-sm text-slate-500 sm:block">@yield('page_subtitle', 'Acompanhe suas atividades')</p>
                    </div>
                </div>

                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-2 py-1.5 transition hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                        <span class="grid size-10 place-items-center rounded-full bg-brand-100 font-extrabold text-brand-800">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden min-w-0 text-left sm:block">
                            <span class="block max-w-44 truncate text-sm font-bold text-slate-800">{{ auth()->user()->name }}</span>
                            <span class="block text-xs text-slate-500">{{ auth()->user()->perfil->label() }}</span>
                        </span>
                        <svg viewBox="0 0 24 24" class="hidden size-4 text-slate-400 sm:block" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m7 10 5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </summary>

                    <div class="absolute right-0 mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-card">
                        <div class="border-b border-slate-100 px-3 py-2 sm:hidden">
                            <p class="truncate text-sm font-bold text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-red-50 hover:text-red-700">
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M14 8V5a2 2 0 0 0-2-2H5v18h7a2 2 0 0 0 2-2v-3M10 12h11m-3-3 3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Sair do sistema
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl">
                @if (session('status'))
                    <x-alert class="mb-6">{{ session('status') }}</x-alert>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
