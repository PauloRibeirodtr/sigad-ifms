@extends('layouts.app')

@section('title', 'Nova categoria')
@section('page_title', 'Nova categoria')
@section('page_subtitle', 'Organize suas atividades por finalidade')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-5">
            <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition hover:text-brand-900">
                <span aria-hidden="true">←</span>
                Voltar para categorias
            </a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="mb-7">
                <p class="text-sm font-bold text-brand-700">Cadastro pessoal</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Dados da categoria</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">A categoria será vinculada apenas à sua conta e ficará ativa imediatamente.</p>
            </div>

            <form method="POST" action="{{ route('categories.store') }}" class="grid gap-7">
                @csrf
                <x-category-form-fields />

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('categories.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>Cadastrar categoria</x-primary-button>
                </div>
            </form>
        </section>
    </div>
@endsection
