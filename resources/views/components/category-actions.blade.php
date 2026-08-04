@props(['category'])

<a href="{{ route('categories.edit', $category) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-100">Editar</a>

@if ($category->ativa)
    <form method="POST" action="{{ route('categories.deactivate', $category) }}" data-confirm="Desativar a categoria {{ $category->nome }}? Ela continuará visível nos registros antigos.">
        @csrf
        @method('PATCH')
        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-red-200 px-3 text-xs font-bold text-red-700 transition hover:bg-red-50">Desativar</button>
    </form>
@else
    <form method="POST" action="{{ route('categories.activate', $category) }}" data-confirm="Ativar novamente a categoria {{ $category->nome }}?">
        @csrf
        @method('PATCH')
        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-brand-200 px-3 text-xs font-bold text-brand-700 transition hover:bg-brand-50">Ativar</button>
    </form>
@endif
