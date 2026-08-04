<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#195a36">

    <title>@yield('title', 'Acesso') — SIGAD</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="grid min-h-screen lg:grid-cols-[minmax(28rem,0.9fr)_minmax(34rem,1.1fr)]">
        <section class="relative hidden overflow-hidden bg-brand-900 px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-20 xl:py-14">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_24%,rgba(88,168,115,0.42),transparent_32%),radial-gradient(circle_at_78%_72%,rgba(255,255,255,0.12),transparent_30%)]"></div>
            <div class="absolute -left-24 bottom-14 size-80 rounded-full border border-white/10"></div>
            <div class="absolute -left-10 bottom-28 size-48 rounded-full border border-white/10"></div>

            <x-application-logo inverted class="relative z-10"/>

            <div class="relative z-10 max-w-xl pb-12">
                <span class="inline-flex rounded-full border border-white/15 bg-white/8 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-brand-100">
                    Organização institucional
                </span>
                <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight xl:text-5xl">
                    Atividades organizadas.<br>
                    Acompanhamento simples.
                </h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-brand-100/85 xl:text-lg">
                    Registre demandas, acompanhe movimentações e consolide seus Planos de Trabalho em um só lugar.
                </p>
            </div>

            <p class="relative z-10 text-xs text-brand-200/65">Sistema Integrado de Gestão de Atividades Docentes</p>
        </section>

        <section class="flex min-h-screen items-center justify-center bg-white px-5 py-10 sm:px-8 lg:bg-slate-50">
            <div class="w-full max-w-md">
                <x-application-logo class="mb-10 lg:hidden"/>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-9">
                    @yield('content')
                </div>

                <p class="mt-6 text-center text-xs text-slate-400">
                    Acesso restrito a usuários autorizados.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
