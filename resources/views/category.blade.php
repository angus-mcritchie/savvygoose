<x-layouts.app>

    @php
        $label = config('tools.categories.'.$categoryKey);
        $tools = collect(config('tools.tools'))->where('category', $categoryKey)->values();
    @endphp

    <div class="mx-auto max-w-[1200px]">

        <div class="mb-12">
            <p class="mb-3 text-xs font-medium uppercase tracking-[0.2em] text-violet-500 dark:text-violet-300">
                {{ $tools->count() }} {{ \Illuminate\Support\Str::plural('tool', $tools->count()) }} &middot; free forever
            </p>
            <flux:heading class="!text-3xl !font-semibold tracking-tight sm:!text-4xl" level="1">
                <span class="bg-gradient-to-r from-rose-400 via-violet-500 to-teal-400 bg-clip-text text-transparent dark:from-rose-300 dark:via-violet-400 dark:to-teal-300">
                    {{ $label }}
                </span>
            </flux:heading>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            @foreach ($tools as $tool)
                <flux:link
                    class="!grid gap-8 rounded-lg border border-black/10 p-8 px-8 py-12 !no-underline transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-white/10"
                    href="{{ route($tool['slug']) }}"
                    wire:navigate
                >
                    <x-tool-icon :icon="$tool['icon']" />

                    <div>
                        <flux:heading class="!text-xl !font-bold">
                            {{ $tool['name'] }}
                        </flux:heading>
                        <flux:subheading>
                            {{ $tool['tagline'] }}
                        </flux:subheading>
                    </div>
                </flux:link>
            @endforeach
        </div>
    </div>
</x-layouts.app>
