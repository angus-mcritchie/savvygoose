<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900" container>
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a class="ml-2 mr-5 flex items-center space-x-2 !py-0 lg:ml-0" href="{{ route('dashboard') }}" wire:navigate>
                <x-app-logo class="size-8" href="/"></x-app-logo>
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">
                {{-- blade-formatter-disable --}}
            <flux:navbar.item href="{{ route('barcode-generator') }}" :current="request()->routeIs('barcode-generator')" wire:navigate>
                {{ __('Barcode Generator') }}
            </flux:navbar.item>
            <flux:navbar.item href="{{ route('percentage-calculator') }}" :current="request()->routeIs('percentage-calculator')" wire:navigate>
                {{ __('Percentage Calculator') }}
            </flux:navbar.item>
            <flux:navbar.item href="{{ route('character-counter') }}" :current="request()->routeIs('character-counter')" wire:navigate>
                {{ __('Character Counter') }}
            </flux:navbar.item>
            {{-- blade-formatter-enable --}}
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
                <x-app-logo class="size-8" href="#"></x-app-logo>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Tools">
                    {{-- blade-formatter-disable --}}
                <flux:navlist.item href="{{ route('barcode-generator') }}" :current="request()->routeIs('barcode-generator')" wire:navigate>
                    {{ __('Barcode Generator') }}
                </flux:navlist.item>
                <flux:navlist.item href="{{ route('percentage-calculator') }}" :current="request()->routeIs('percentage-calculator')" wire:navigate>
                    {{ __('Percentage Calculator') }}
                </flux:navlist.item>
                <flux:navlist.item href="{{ route('character-counter') }}" :current="request()->routeIs('character-counter')" wire:navigate>
                    {{ __('Character Counter') }}
                </flux:navlist.item>
                {{-- blade-formatter-enable --}}
                </flux:navlist.group>
            </flux:navlist>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
        @livewireScriptConfig
    </body>

</html>
