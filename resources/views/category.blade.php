<x-layouts.app>

    @php
        $label = config('tools.categories.'.$categoryKey);
        $tools = collect(config('tools.tools'))->where('category', $categoryKey)->values();
    @endphp

    <div class="mx-auto max-w-[1200px]">

        <div class="mb-12">
            <flux:heading class="mb-2" level="1" size="xl">
                {{ $label }}
            </flux:heading>
            <flux:heading class="font-normal opacity-70" level="2">
                {{ $tools->count() }} {{ \Illuminate\Support\Str::plural('tool', $tools->count()) }} in this category.
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
