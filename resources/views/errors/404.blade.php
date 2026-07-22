<x-layouts.app>
    @push('head')
        <meta name="robots" content="noindex" />
    @endpush

    @php
        $popular = collect(config('tools.tools'))
            ->whereIn('slug', ['barcode-generator', 'qr-code-generator', 'json-formatter', 'timestamp-converter', 'password-generator', 'color-converter'])
            ->values();
    @endphp

    <div class="mx-auto max-w-2xl py-10 text-center">
        <flux:heading level="1" size="xl" class="mb-2">Page not found</flux:heading>
        <flux:heading level="2" class="mb-8 font-normal opacity-70">
            That page doesn't exist, but plenty of tools do. Try one of these, or head back home.
        </flux:heading>

        <div class="grid gap-3 text-left sm:grid-cols-2">
            @foreach ($popular as $tool)
                <a
                    href="{{ route($tool['slug']) }}"
                    wire:navigate
                    class="flex items-center gap-3 rounded-lg border border-black/10 p-4 transition hover:border-black/20 dark:border-white/10 dark:hover:border-white/20"
                >
                    <x-tool-icon :icon="$tool['icon']" class="size-6" />
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $tool['name'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            <flux:button href="{{ route('dashboard') }}" wire:navigate icon="home">Back to all tools</flux:button>
        </div>
    </div>
</x-layouts.app>
