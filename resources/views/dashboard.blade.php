<x-layouts.app>

    <div class="mx-auto max-w-[1200px]">

        <div class="mb-16 flex flex-col items-center text-center">
            <p class="mb-5 text-xs font-medium uppercase tracking-[0.2em] text-violet-500 dark:text-violet-300">
                {{ count(config('tools.tools')) }} tools &middot; no sign-up
            </p>

            <flux:heading class="mb-4 max-w-2xl !text-4xl !font-semibold !leading-tight tracking-tight sm:!text-5xl" level="1">
                Helpful tools,
                <span class="bg-gradient-to-r from-rose-400 via-violet-500 to-teal-400 bg-clip-text text-transparent dark:from-rose-300 dark:via-violet-400 dark:to-teal-300">
                    free forever.
                </span>
            </flux:heading>

            <flux:subheading class="max-w-xl !text-base sm:!text-lg" level="2">
                A small, growing collection of utilities. They run in your browser, ask for nothing, and never see your data.
            </flux:subheading>
        </div>

        @php
            $categories = config('tools.categories');
            $tools = collect(config('tools.tools'));
            $toolsByCategory = $tools->groupBy('category');
            $popularTools = $tools
                ->whereIn('slug', [
                    'percentage-calculator',
                    'markdown-converter',
                    'mermaid-editor',
                    'barcode-generator',
                    'password-generator',
                ])
                ->sortBy(fn ($tool) => array_search($tool['slug'], [
                    'percentage-calculator',
                    'markdown-converter',
                    'mermaid-editor',
                    'barcode-generator',
                    'password-generator',
                ]));
        @endphp

        <div class="grid gap-12">
            <section>
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <flux:heading level="2" size="lg">
                            Most popular
                        </flux:heading>
                        <flux:subheading>
                            Start with the tools people reach for most often.
                        </flux:subheading>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-5">
                    @foreach ($popularTools as $tool)
                        <flux:link
                            class="!grid gap-3 rounded-lg border border-violet-500/20 bg-violet-500/[0.03] p-5 !no-underline transition duration-300 hover:-translate-y-1 hover:border-violet-500/40 hover:shadow-xl dark:border-violet-300/20 dark:bg-violet-300/[0.04] dark:hover:border-violet-300/40"
                            href="{{ route($tool['slug']) }}"
                            wire:navigate
                        >
                            <x-tool-icon class="size-9" :icon="$tool['icon']" />

                            <div>
                                <flux:heading class="!font-semibold">
                                    {{ $tool['name'] }}
                                </flux:heading>
                                <flux:subheading class="!text-sm">
                                    {{ $tool['tagline'] }}
                                </flux:subheading>
                            </div>
                        </flux:link>
                    @endforeach
                </div>
            </section>

            @foreach ($categories as $key => $label)
                @if ($toolsByCategory->has($key))
                    <section>
                        <div class="mb-4 flex items-baseline justify-between gap-4">
                            <flux:heading level="2" size="lg">
                                {{ $label }}
                            </flux:heading>
                            <flux:link
                                class="text-sm !no-underline opacity-70 hover:opacity-100"
                                href="{{ route('category.'.$key) }}"
                                wire:navigate
                            >
                                {{ __('All :label', ['label' => $label]) }}
                            </flux:link>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($toolsByCategory[$key] as $tool)
                                <flux:link
                                    class="!flex items-center gap-4 rounded-lg border border-black/10 p-4 !no-underline transition duration-300 hover:border-black/25 hover:bg-zinc-50 dark:border-white/10 dark:hover:border-white/25 dark:hover:bg-white/5"
                                    href="{{ route($tool['slug']) }}"
                                    wire:navigate
                                >
                                    <x-tool-icon class="size-9 shrink-0" :icon="$tool['icon']" />

                                    <div class="min-w-0">
                                        <flux:heading class="!font-semibold">
                                            {{ $tool['name'] }}
                                        </flux:heading>
                                        <flux:subheading class="!text-sm">
                                            {{ $tool['tagline'] }}
                                        </flux:subheading>
                                    </div>
                                </flux:link>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div>
    </div>
</x-layouts.app>
