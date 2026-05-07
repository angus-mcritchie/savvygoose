<x-layouts.app.header>
    <flux:main>
        {{ $slot }}
    </flux:main>

    <flux:footer class="mt-16 border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mx-auto flex max-w-[1200px] flex-col items-center justify-between gap-3 text-sm text-zinc-600 sm:flex-row dark:text-zinc-400">
            <p class="flex items-center gap-1.5">
                Made for free with
                <flux:icon.heart variant="solid" class="size-4 text-rose-500" />
            </p>
            <div class="flex items-center gap-5">
                <flux:link href="https://github.com/angus-mcritchie/savvygoose" target="_blank" rel="noopener" variant="subtle">
                    Open source — PRs welcome
                </flux:link>
                <flux:link href="https://buymeacoffee.com/angus_mcritchie" target="_blank" rel="noopener" variant="subtle">
                    Buy me a coffee
                </flux:link>
            </div>
        </div>
    </flux:footer>
</x-layouts.app.header>
