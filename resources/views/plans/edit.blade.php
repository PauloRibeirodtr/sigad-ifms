@extends('layouts.app')

@section('title', 'Editar Plano de Trabalho')
@section('page_title', 'Editar Plano de Trabalho')
@section('page_subtitle', 'Atualize os dados sem reduzir o período')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-5">
            <a href="{{ route('plans.show', $plan) }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition hover:text-brand-900"><span aria-hidden="true">←</span> Voltar para o plano</a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-brand-700">Plano #{{ $plan->id }}</p>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Editar dados e período</h2>
                </div>
                <x-plan-status-badge :status="$plan->status" />
            </div>

            <form method="POST" action="{{ route('plans.update', $plan) }}" class="grid gap-7">
                @csrf
                @method('PUT')
                <x-plan-form-fields :plan="$plan" editing />

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('plans.show', $plan) }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</a>
                    <x-primary-button>Salvar alterações</x-primary-button>
                </div>
            </form>
        </section>
    </div>
@endsection
