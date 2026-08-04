@extends('layouts.app')

@section('title', 'Editar categoria')
@section('page_title', 'Editar categoria')
@section('page_subtitle', 'Atualize o nome e a descrição')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-5">
            <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition hover:text-brand-900">
                <span aria-hidden="true">←</span>
                Voltar para categorias
            </a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-brand-700">Categoria #{{ $category->id }}</p>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Editar dados</h2>
                </div>
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold {{ $category->ativa ? 'bg-brand-50 text-brand-700' : 'bg-red-50 text-red-700' }}">
                    {{ $category->ativa ? 'Ativa' : 'Inativa' }}
                </span>
            </div>

            <form method="POST" action="{{ route('categories.update', $category) }}" class="grid gap-7">
                @csrf
                @method('PUT')
                <x-category-form-fields :category="$category" />

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('categories.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>Salvar alterações</x-primary-button>
                </div>
            </form>
        </section>
    </div>
@endsection
