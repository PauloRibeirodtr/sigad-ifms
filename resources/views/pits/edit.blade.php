@extends('layouts.app')

@section('title', 'Editar PIT '.$pit->nome)
@section('page_title', 'Editar PIT')
@section('page_subtitle', 'Atualize a identificação e a vigência')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-5"><a href="{{ route('pits.show', $pit) }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700"><span aria-hidden="true">←</span> Voltar para o PIT</a></div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
            <div class="mb-7 flex items-start justify-between gap-4">
                <div><p class="text-sm font-bold text-brand-700">PIT {{ $pit->nome }}</p><h2 class="mt-1 text-2xl font-extrabold text-slate-950">Editar período</h2></div>
                <x-plan-status-badge :status="$pit->status" />
            </div>

            <form method="POST" action="{{ route('pits.update', $pit) }}" class="grid gap-7">
                @csrf
                @method('PUT')
                <x-pit-form-fields :pit="$pit" editing />
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('pits.show', $pit) }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600">Cancelar</a>
                    <x-primary-button>Salvar alterações</x-primary-button>
                </div>
            </form>
        </section>
    </div>
@endsection
