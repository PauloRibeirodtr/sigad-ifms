@extends('layouts.app')

@section('title', 'Senha temporária')
@section('page_title', 'Senha temporária')
@section('page_subtitle', 'Copie a credencial antes de sair desta tela')

@section('content')
    <div class="mx-auto max-w-3xl">
        <section class="overflow-hidden rounded-3xl border border-brand-200 bg-white shadow-card">
            <div class="bg-brand-800 px-6 py-7 text-white sm:px-8">
                <p class="text-sm font-bold text-brand-100">{{ $reason === 'created' ? 'Usuário cadastrado com sucesso' : 'Senha redefinida com sucesso' }}</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Senha temporária de {{ $user->name }}</h2>
            </div>

            <div class="grid gap-6 p-6 sm:p-8">
                <x-alert type="info">Esta senha é exibida somente nesta tela. Ela não é armazenada em texto puro e não poderá ser recuperada depois.</x-alert>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Senha temporária</p>
                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <code data-temporary-password class="min-w-0 flex-1 break-all rounded-xl bg-white px-4 py-4 text-lg font-extrabold tracking-wider text-slate-950 ring-1 ring-slate-200">{{ $temporaryPassword }}</code>
                        <button type="button" data-copy-password class="inline-flex min-h-12 items-center justify-center rounded-xl border border-brand-600 px-5 text-sm font-bold text-brand-700 transition hover:bg-brand-50">Copiar senha</button>
                    </div>
                </div>

                <div class="rounded-2xl border border-warning-100 bg-warning-50 p-5 text-sm leading-6 text-warning-700">
                    No primeiro acesso, o usuário será obrigado a definir uma nova senha antes de acessar o sistema.
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-6">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white transition hover:bg-brand-800">Concluir e voltar</a>
                </div>
            </div>
        </section>
    </div>
@endsection
