@extends('layouts.app')

@section('title', 'Categorias')
@section('page_title', 'Categorias')
@section('page_subtitle', 'Organize o banco de atividades da sua conta')

@section('content')
    <div class="grid gap-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-brand-700">Categorias particulares</p>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">{{ $categories->total() }} {{ $categories->total() === 1 ? 'categoria encontrada' : 'categorias encontradas' }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Cada categoria pertence somente a você, inclusive em contas administrativas.</p>
                </div>
                <a href="{{ route('categories.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800">
                    <span class="text-xl leading-none" aria-hidden="true">+</span>
                    Nova categoria
                </a>
            </div>

            <form method="GET" action="{{ route('categories.index') }}" class="mt-6 grid gap-3 border-t border-slate-100 pt-6 md:grid-cols-[minmax(0,1fr)_14rem_auto]">
                <x-form-input name="nome" label="Nome" :value="request('nome')" placeholder="Buscar categoria" />

                <div class="grid gap-2">
                    <label for="status" class="text-sm font-semibold text-slate-700">Status</label>
                    <select id="status" name="status" class="min-h-12 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                        <option value="">Todas</option>
                        <option value="ativa" @selected(request('status') === 'ativa')>Ativas</option>
                        <option value="inativa" @selected(request('status') === 'inativa')>Inativas</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex min-h-12 flex-1 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white transition hover:bg-brand-800">Filtrar</button>
                    <a href="{{ route('categories.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Limpar</a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-card">
            @if ($categories->isEmpty())
                <div class="px-6 py-16 text-center">
                    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-brand-700" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 7.5 10.5 3H20v9.5L13.5 19 4 9.5v-2Z" stroke-linejoin="round"/><circle cx="15.5" cy="7.5" r="1.25"/>
                        </svg>
                    </span>
                    <h3 class="mt-4 text-lg font-extrabold text-slate-900">Nenhuma categoria encontrada</h3>
                    <p class="mt-1 text-sm text-slate-500">Cadastre uma categoria ou revise os filtros aplicados.</p>
                </div>
            @else
                <div class="hidden overflow-x-auto lg:block">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-extrabold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Categoria</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Atualização</th>
                                <th class="px-6 py-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($categories as $category)
                                <tr class="align-top transition hover:bg-slate-50/70">
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-slate-950">{{ $category->nome }}</p>
                                        <p class="mt-1 max-w-2xl text-xs leading-5 text-slate-500">{{ $category->descricao ?: 'Sem descrição.' }}</p>
                                    </td>
                                    <td class="px-5 py-5">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $category->ativa ? 'text-brand-700' : 'text-red-700' }}">
                                            <span class="size-2 rounded-full {{ $category->ativa ? 'bg-brand-500' : 'bg-red-500' }}"></span>
                                            {{ $category->ativa ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-5 text-xs text-slate-600">{{ $category->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap justify-end gap-2"><x-category-actions :category="$category" /></div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid gap-4 p-4 sm:grid-cols-2 lg:hidden sm:p-5">
                    @foreach ($categories as $category)
                        <article class="grid content-between gap-5 rounded-2xl border border-slate-200 p-5">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-extrabold text-slate-950">{{ $category->nome }}</h3>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $category->ativa ? 'bg-brand-50 text-brand-700' : 'bg-red-50 text-red-700' }}">{{ $category->ativa ? 'Ativa' : 'Inativa' }}</span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-slate-500">{{ $category->descricao ?: 'Sem descrição.' }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-4"><x-category-actions :category="$category" /></div>
                        </article>
                    @endforeach
                </div>

                @if ($categories->hasPages())
                    <div class="border-t border-slate-200 px-5 py-5 sm:px-6">{{ $categories->links() }}</div>
                @endif
            @endif
        </section>
    </div>
@endsection
