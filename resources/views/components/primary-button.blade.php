<button {{ $attributes->class(['inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-700 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800 active:translate-y-px disabled:cursor-not-allowed disabled:opacity-60'])->merge(['type' => 'submit']) }}>
    {{ $slot }}
</button>
