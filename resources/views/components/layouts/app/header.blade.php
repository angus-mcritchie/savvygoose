<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $categories = config('tools.categories');
            $toolsByCategory = collect(config('tools.tools'))->groupBy('category');
        @endphp

        <flux:header class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900" container>
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" aria-label="Open navigation" />

            <a class="ml-2 mr-5 flex items-center space-x-2 !py-0 lg:ml-0" href="{{ route('dashboard') }}" wire:navigate>
                <x-app-logo class="size-8" href="/"></x-app-logo>
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:dropdown>
                    <flux:navbar.item icon:trailing="chevron-down" :current="request()->routeIs('category.*')">
                        {{ __('Tools') }}
                    </flux:navbar.item>

                    <flux:popover class="columns-3 gap-8 !p-5">
                        @foreach ($categories as $key => $label)
                            @if ($toolsByCategory->has($key))
                                <div class="mb-6 break-inside-avoid last:mb-0">
                                    <a
                                        class="mb-1 block px-2 text-xs font-medium uppercase tracking-wider text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white"
                                        href="{{ route('category.'.$key) }}"
                                        wire:navigate
                                    >
                                        {{ __($label) }}
                                    </a>
                                    <div class="grid">
                                        @foreach ($toolsByCategory[$key] as $tool)
                                            <flux:navmenu.item
                                                href="{{ route($tool['slug']) }}"
                                                :icon="$tool['icon']['type'] === 'flux' ? $tool['icon']['name'] : null"
                                                wire:navigate
                                            >
                                                {{ __($tool['name']) }}
                                            </flux:navmenu.item>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </flux:popover>
                </flux:dropdown>
            </flux:navbar>
            <x-command-palette />

            <flux:button
                href="https://buymeacoffee.com/angus_mcritchie"
                target="_blank"
                rel="noopener"
                icon="heart"
                variant="subtle"
            >
                {{ __('Buy me a coffee') }}
            </flux:button>
            <flux:button
                x-data
                x-on:click="$flux.dark = ! $flux.dark"
                icon="moon"
                variant="subtle"
                aria-label="Toggle dark mode"
            />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar class="border-r border-zinc-200 bg-zinc-50 lg:hidden dark:border-zinc-700 dark:bg-zinc-900" stashable sticky>
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" aria-label="Close navigation" />

            <a class="ml-1 flex items-center space-x-2" href="{{ route('dashboard') }}" wire:navigate>
                <x-app-logo class="size-8" href="/"></x-app-logo>
            </a>

            <flux:navlist variant="outline">
                @foreach ($categories as $key => $label)
                    @if ($toolsByCategory->has($key))
                        <flux:navlist.group :heading="$label">
                            <flux:navlist.item href="{{ route('category.'.$key) }}" :current="request()->routeIs('category.'.$key)" icon="squares-2x2" wire:navigate>
                                {{ __('All :label', ['label' => $label]) }}
                            </flux:navlist.item>
                            @foreach ($toolsByCategory[$key] as $tool)
                                <flux:navlist.item
                                    href="{{ route($tool['slug']) }}"
                                    :current="request()->routeIs($tool['slug'])"
                                    :icon="$tool['icon']['type'] === 'flux' ? $tool['icon']['name'] : null"
                                    wire:navigate
                                >
                                    {{ __($tool['name']) }}
                                </flux:navlist.item>
                            @endforeach
                        </flux:navlist.group>
                    @endif
                @endforeach
            </flux:navlist>

            <flux:spacer />

            <flux:navlist>
                <flux:navlist.item href="https://buymeacoffee.com/angus_mcritchie" target="_blank" rel="noopener" icon="heart">
                    {{ __('Buy me a coffee') }}
                </flux:navlist.item>
            </flux:navlist>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
        @livewireScriptConfig
    </body>

</html>
