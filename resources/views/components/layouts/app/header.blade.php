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
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a class="ml-2 mr-5 flex items-center space-x-2 !py-0 lg:ml-0" href="{{ route('dashboard') }}" wire:navigate>
                <x-app-logo class="size-8" href="/"></x-app-logo>
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">
                @foreach ($categories as $key => $label)
                    @if ($toolsByCategory->has($key))
                        <flux:dropdown>
                            <flux:navbar.item icon:trailing="chevron-down" :current="request()->routeIs('category.'.$key)">
                                {{ __($label) }}
                            </flux:navbar.item>

                            <flux:menu>
                                <flux:menu.item href="{{ route('category.'.$key) }}" icon="squares-2x2" wire:navigate>
                                    {{ __('All :label', ['label' => $label]) }}
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach ($toolsByCategory[$key] as $tool)
                                    <flux:menu.item href="{{ route($tool['slug']) }}" wire:navigate>
                                        {{ __($tool['name']) }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    @endif
                @endforeach
            </flux:navbar>
            <flux:button
                class="ml-auto"
                x-data
                x-on:click="$flux.dark = ! $flux.dark"
                icon="moon"
                variant="subtle"
            />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar class="border-r border-zinc-200 bg-zinc-50 lg:hidden dark:border-zinc-700 dark:bg-zinc-900" stashable sticky>
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

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
                                <flux:navlist.item href="{{ route($tool['slug']) }}" :current="request()->routeIs($tool['slug'])" wire:navigate>
                                    {{ __($tool['name']) }}
                                </flux:navlist.item>
                            @endforeach
                        </flux:navlist.group>
                    @endif
                @endforeach
            </flux:navlist>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
        @livewireScriptConfig
    </body>

</html>
