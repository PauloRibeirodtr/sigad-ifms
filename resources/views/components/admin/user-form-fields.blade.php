@props(['user' => null, 'includeAccess' => false])

<div class="grid gap-5 md:grid-cols-2">
    <x-form-input name="name" label="Nome" :value="$user?->name" required autocomplete="name" />
    <x-form-input name="email" label="E-mail" type="email" :value="$user?->email" required autocomplete="email" />

    @if ($includeAccess)
        <div class="grid gap-2">
            <label for="perfil" class="text-sm font-semibold text-slate-700">Perfil</label>
            <select id="perfil" name="perfil" required class="min-h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm transition hover:border-slate-300 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                @foreach (\App\Enums\UserProfile::cases() as $profile)
                    <option value="{{ $profile->value }}" @selected(old('perfil', \App\Enums\UserProfile::Usuario->value) === $profile->value)>
                        {{ $profile->label() }}
                    </option>
                @endforeach
            </select>
            @error('perfil')
                <p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-2">
            <label for="ativo" class="text-sm font-semibold text-slate-700">Status inicial</label>
            <select id="ativo" name="ativo" required class="min-h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm transition hover:border-slate-300 focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                <option value="1" @selected(old('ativo', '1') === '1')>Ativo</option>
                <option value="0" @selected(old('ativo') === '0')>Inativo</option>
            </select>
            @error('ativo')
                <p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>
