@props(['user'])

@if ($user->ativo && auth()->user()->isNot($user))
    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" data-confirm="Desativar a conta de {{ $user->name }}? As sessões ativas serão encerradas.">
        @csrf
        @method('PATCH')
        <button type="submit" class="inline-flex min-h-9 items-center rounded-lg border border-red-200 px-3 text-xs font-bold text-red-700 transition hover:bg-red-50">Desativar</button>
    </form>
@elseif (! $user->ativo)
    <form method="POST" action="{{ route('admin.users.activate', $user) }}" data-confirm="Ativar a conta de {{ $user->name }}?">
        @csrf
        @method('PATCH')
        <button type="submit" class="inline-flex min-h-9 items-center rounded-lg border border-brand-200 px-3 text-xs font-bold text-brand-700 transition hover:bg-brand-50">Ativar</button>
    </form>
@endif

@if ($user->perfil === \App\Enums\UserProfile::Usuario)
    <form method="POST" action="{{ route('admin.users.promote', $user) }}" data-confirm="Promover {{ $user->name }} a administrador?">
        @csrf
        @method('PATCH')
        <button type="submit" class="inline-flex min-h-9 items-center rounded-lg border border-brand-200 px-3 text-xs font-bold text-brand-700 transition hover:bg-brand-50">Promover</button>
    </form>
@elseif (auth()->user()->isNot($user))
    <form method="POST" action="{{ route('admin.users.demote', $user) }}" data-confirm="Rebaixar {{ $user->name }} para usuário comum?">
        @csrf
        @method('PATCH')
        <button type="submit" class="inline-flex min-h-9 items-center rounded-lg border border-warning-100 px-3 text-xs font-bold text-warning-700 transition hover:bg-warning-50">Rebaixar</button>
    </form>
@endif

@if (auth()->user()->isNot($user))
    <form method="POST" action="{{ route('admin.users.password.reset', $user) }}" data-confirm="Gerar uma nova senha temporária para {{ $user->name }}? A senha atual deixará de funcionar.">
        @csrf
        <button type="submit" class="inline-flex min-h-9 items-center rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-700 transition hover:bg-slate-100">Redefinir senha</button>
    </form>
@endif
