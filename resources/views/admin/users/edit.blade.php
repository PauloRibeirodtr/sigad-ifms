@extends('layouts.app')

@section('title', 'Editar usuário')
@section('page_title', 'Editar usuário')
@section('page_subtitle', 'Atualize os dados básicos da conta')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-5">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition hover:text-brand-900">
                <span aria-hidden="true">←</span>
                Voltar para usuários
            </a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="mb-7">
                <p class="text-sm font-bold text-brand-700">Conta #{{ $user->id }}</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Editar nome e e-mail</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Perfil, status e senha são alterados pelas ações protegidas da listagem.</p>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-7">
                @csrf
                @method('PUT')
                <x-admin.user-form-fields :user="$user" />

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>Salvar alterações</x-primary-button>
                </div>
            </form>
        </section>
    </div>
@endsection
