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
            $toolsByCategory = collect(config('tools.tools'))->groupBy('category');
        @endphp

        <div class="grid gap-12">
            @foreach ($categories as $key => $label)
                @if ($toolsByCategory->has($key))
                    <section>
                        <flux:heading class="mb-4" level="2" size="lg">
                            {{ $label }}
                        </flux:heading>
                        <div class="grid gap-8 lg:grid-cols-3">
                            @foreach ($toolsByCategory[$key] as $tool)
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
                    </section>
                @endif
            @endforeach
        </div>
    </div>
</x-layouts.app>
