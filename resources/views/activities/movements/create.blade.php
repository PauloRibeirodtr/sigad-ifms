@extends('layouts.app')

@section('title', 'Nova movimentação')
@section('page_title', 'Nova movimentação')
@section('page_subtitle', $atividade->titulo)

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-5">
            <a href="{{ route('plans.activities.show', [$plano, $atividade]) }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700">← Voltar para a atividade</a>
        </div>

        <form method="POST" action="{{ route('plans.activities.movements.store', [$plano, $atividade]) }}" enctype="multipart/form-data" class="grid gap-6">
            @csrf
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
                <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-brand-700">Atividade #{{ $atividade->id }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-slate-950">Registrar ação realizada</h2>
                        <p class="mt-2 text-sm text-slate-500">Datas permitidas: {{ $atividade->data_atividade->format('d/m/Y') }} a {{ $plano->data_final->format('d/m/Y') }}.</p>
                    </div>
                    <x-activity-status-badge :status="$atividade->status" />
                </div>

                <x-movement-fields :plan="$plano" :activity="$atividade" />
            </section>

            <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-card sm:flex-row sm:justify-end">
                <a href="{{ route('plans.activities.show', [$plano, $atividade]) }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600">Cancelar</a>
                <x-primary-button>Adicionar movimentação</x-primary-button>
            </div>
        </form>
    </div>
@endsection
