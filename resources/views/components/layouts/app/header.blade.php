<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900" container>
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <a class="ml-2 mr-5 flex items-center space-x-2 lg:ml-0" href="{{ route('dashboard') }}" wire:navigate>
            <x-app-logo class="size-8" href="/"></x-app-logo>
        </a>

        <flux:navbar class="-mb-px max-lg:hidden">
            {{-- blade-formatter-disable --}}
            <flux:navbar.item href="{{ route('dashboard') }}" :current="request()->routeIs('barcode-generator')" wire:navigate>
                {{ __('Barcode Generator') }}
            </flux:navbar.item>
            <flux:navbar.item href="{{ route('dashboard') }}" :current="request()->routeIs('percentage-calculator')" wire:navigate>
                {{ __('Percentage Calculator') }}
            </flux:navbar.item>
            <flux:navbar.item href="{{ route('dashboard') }}" :current="request()->routeIs('character-counter')" wire:navigate>
                {{ __('Character Counter') }}
            </flux:navbar.item>
            {{-- blade-formatter-enable --}}
        </flux:navbar>

        <flux:spacer />

        <flux:navbar class="py-0! mr-1.5 space-x-0.5">
            <flux:tooltip content="Search" position="bottom">
                <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" label="Search" />
            </flux:tooltip>
            <flux:tooltip content="Repository" position="bottom">
                <flux:navbar.item class="h-10 max-lg:hidden [&>div>svg]:size-5" icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank" label="Repository" />
            </flux:tooltip>
            <flux:tooltip content="Documentation" position="bottom">
                <flux:navbar.item class="h-10 max-lg:hidden [&>div>svg]:size-5" icon="book-open-text" href="https://laravel.com/docs/starter-kits" target="_blank" label="Documentation" />
            </flux:tooltip>
        </flux:navbar>

        <!-- Desktop User Menu -->
        <flux:dropdown position="top" align="end">
            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar class="border-r border-zinc-200 bg-zinc-50 lg:hidden dark:border-zinc-700 dark:bg-zinc-900" stashable sticky>
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a class="ml-1 flex items-center space-x-2" href="{{ route('dashboard') }}" wire:navigate>
            <x-app-logo class="size-8" href="#"></x-app-logo>
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group heading="Platform">
                {{-- blade-formatter-disable-next-line --}}
                <flux:navlist.item icon="layout-grid" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits" target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist>
    </flux:sidebar>

    {{ $slot }}

    @fluxScripts
</body>

</html>
