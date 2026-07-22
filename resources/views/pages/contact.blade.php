<x-layouts.app>
    <div class="mx-auto max-w-3xl">
        <flux:heading level="1" size="xl" class="mb-2">Contact</flux:heading>
        <flux:heading level="2" class="mb-8 font-normal opacity-70">Found a bug, want a new tool, or have a question? Here's how to reach the project.</flux:heading>

        <div class="grid gap-6 leading-relaxed text-zinc-700 dark:text-zinc-300">
            <p>
                Savvy Goose is an open-source project, so the fastest way to get something fixed or added is
                through GitHub. Bug reports, feature ideas, and pull requests are all welcome.
            </p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <a
                href="https://github.com/angus-mcritchie/savvygoose/issues"
                target="_blank"
                rel="noopener"
                class="flex items-start gap-3 rounded-lg border border-black/10 p-5 transition hover:border-black/20 dark:border-white/10 dark:hover:border-white/20"
            >
                <flux:icon.exclamation-circle class="size-6 shrink-0 text-zinc-500" />
                <span>
                    <span class="block font-medium text-zinc-900 dark:text-zinc-100">Open an issue</span>
                    <span class="block text-sm text-zinc-600 dark:text-zinc-400">Report a bug or request a tool.</span>
                </span>
            </a>
            <a
                href="https://github.com/angus-mcritchie/savvygoose"
                target="_blank"
                rel="noopener"
                class="flex items-start gap-3 rounded-lg border border-black/10 p-5 transition hover:border-black/20 dark:border-white/10 dark:hover:border-white/20"
            >
                <flux:icon.code-bracket class="size-6 shrink-0 text-zinc-500" />
                <span>
                    <span class="block font-medium text-zinc-900 dark:text-zinc-100">Send a pull request</span>
                    <span class="block text-sm text-zinc-600 dark:text-zinc-400">Browse the source and contribute.</span>
                </span>
            </a>
        </div>
    </div>
</x-layouts.app>
