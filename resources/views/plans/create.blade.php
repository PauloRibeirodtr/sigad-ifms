@extends('layouts.app')

@section('title', 'Novo Plano de Trabalho')
@section('page_title', 'Novo Plano de Trabalho')
@section('page_subtitle', 'Defina o período e o objetivo do plano')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-5">
            <a href="{{ route('plans.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition hover:text-brand-900"><span aria-hidden="true">←</span> Voltar para planos</a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="mb-7">
                <p class="text-sm font-bold text-brand-700">Novo período de trabalho</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Dados do Plano de Trabalho</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">O status será determinado automaticamente pelas datas informadas.</p>
            </div>

            <form method="POST" action="{{ route('plans.store') }}" class="grid gap-7">
                @csrf
                <x-plan-form-fields />

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('plans.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>Cadastrar plano</x-primary-button>
                </div>
            </form>
        </section>
    </div>
@endsection
