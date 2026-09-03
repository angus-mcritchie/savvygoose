{{--
    Reading layout. Deliberately not x-layouts.app: a document someone was sent
    should look like a document, not like a tool page with their content wedged
    into it. No header, no sidebar, no breadcrumbs. The footer below is the only
    way back into the site, so it stays on the page in every state.
--}}
<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <main class="mx-auto max-w-3xl px-6 py-10 sm:py-14">
            {{ $slot }}

            <footer class="mt-16 border-t border-black/10 pt-6 text-sm text-zinc-500 print:hidden dark:border-white/10 dark:text-zinc-400">
                <div class="flex flex-wrap items-center justify-between gap-x-5 gap-y-2">
                    <p class="flex items-center gap-1.5">
                        Made with
                        <flux:icon.heart variant="solid" class="size-4 text-rose-500" />
                        on
                        <flux:link href="{{ route('dashboard') }}" wire:navigate variant="subtle">Savvy Goose</flux:link>
                    </p>
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                        <flux:link href="{{ route('markdown-converter') }}" wire:navigate variant="subtle">Markdown Converter</flux:link>
                        <flux:link href="{{ route('privacy') }}" wire:navigate variant="subtle">Privacy</flux:link>
                    </div>
                </div>
            </footer>
        </main>

        @fluxScripts
        @livewireScriptConfig
    </body>

</html>
