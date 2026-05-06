<x-layouts.app>

    <div class="mx-auto max-w-[1200px]">

        <div class="mb-12 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <img class="mx-auto w-[128px]" src="{{ asset('image/window-cloud.png') }}" width="128" height="128">
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">
                        Helpful Tools, Free Forever
                    </flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        A collection of free tools to help you with your daily tasks.
                    </flux:heading>
                </div>
            </div>
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
