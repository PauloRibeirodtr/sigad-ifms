@extends('layouts.app')

@section('title', 'PITs')
@section('page_title', request()->routeIs('dashboard') ? 'Painel inicial' : 'PITs')
@section('page_subtitle', 'Períodos Individuais de Trabalho')

@section('content')
    <section aria-labelledby="pits-heading">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 id="pits-heading" class="text-xl font-extrabold tracking-tight text-slate-950">Meus PITs</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $pits->total() }} {{ $pits->total() === 1 ? 'PIT cadastrado' : 'PITs cadastrados' }}.</p>
            </div>
            <a href="{{ route('pits.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white">+ Novo PIT</a>
        </div>

        @if ($pits->isEmpty())
            <div class="mt-5 overflow-hidden rounded-3xl border border-dashed border-brand-300 bg-white shadow-card">
                <div class="grid min-h-80 place-items-center px-6 py-12 text-center">
                    <div class="max-w-md">
                        <span class="mx-auto grid size-20 place-items-center rounded-3xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                            <svg viewBox="0 0 24 24" class="size-10" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H10l2 2h5.5A2.5 2.5 0 0 1 20 7.5v10a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-12Z" stroke-linejoin="round"/><path d="M12 10v6m-3-3h6" stroke-linecap="round"/></svg>
                        </span>
                        <h3 class="mt-6 text-xl font-extrabold text-slate-900">Nenhum PIT cadastrado</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Cadastre o primeiro PIT para definir a vigência dos seus PATs.</p>
                        <a href="{{ route('pits.create') }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-700 px-5 text-sm font-bold text-white transition hover:bg-brand-800">Novo PIT</a>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($pits as $pit)
                    <x-pit-card :pit="$pit" />
                @endforeach
            </div>

            @if ($pits->hasPages())
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-card">{{ $pits->links() }}</div>
            @endif
        @endif
    </section>
@endsection
