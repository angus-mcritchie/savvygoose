<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="regexTester">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.magnifying-glass class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Regex Tester</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Build and debug JavaScript regular expressions live.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">1. Pattern</flux:heading>

                <flux:field>
                    <flux:label>Regular expression</flux:label>
                    <div class="flex items-stretch gap-2 font-mono">
                        <span class="grid place-items-center px-2 text-zinc-400">/</span>
                        <flux:input
                            class="grow"
                            x-model="pattern"
                            placeholder="\\b\\w+@\\w+\\.\\w+\\b"
                        />
                        <span class="grid place-items-center px-2 text-zinc-400">/</span>
                        <flux:input class="w-[120px]" x-model="flags" placeholder="g" />
                    </div>
                </flux:field>

                <div class="mt-4 flex flex-wrap gap-2">
                    <template x-for="flag in allFlags" :key="flag">
                        <flux:button
                            type="button"
                            size="xs"
                            x-on:click="toggleFlag(flag)"
                            x-bind:variant="hasFlag(flag) ? 'primary' : 'outline'"
                            x-bind:title="flagDescriptions[flag]"
                        >
                            <span x-text="flag" class="font-mono"></span>
                        </flux:button>
                    </template>
                </div>

                <div
                    x-show="error"
                    x-cloak
                    class="mt-4 rounded-md border border-red-500/40 bg-red-500/10 px-3 py-2 font-mono text-sm text-red-700 dark:text-red-300"
                    x-text="error"
                ></div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">2. Test string</flux:heading>

                <flux:textarea
                    name="test"
                    x-model="test"
                    label="Input"
                    placeholder="Paste or type the text you want to match against…"
                    rows="8"
                    class="font-mono"
                />

                <flux:label class="mt-6 block">Highlighted matches</flux:label>
                <div
                    class="mt-2 min-h-[5rem] whitespace-pre-wrap rounded-md border border-black/10 bg-zinc-50 p-4 font-mono text-sm dark:border-white/10 dark:bg-zinc-900"
                    x-html="highlighted || '<span class=\'opacity-40\'>Matches will be highlighted here.</span>'"
                ></div>

                <div
                    class="mt-3 inline-flex items-center gap-2 rounded-md px-2 py-1 text-sm"
                    x-bind:class="matchCount > 0 ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-zinc-500/10 text-zinc-700 dark:text-zinc-300'"
                    x-show="regex && test"
                    x-cloak
                >
                    <flux:icon.check-circle class="size-4" x-show="matchCount > 0" />
                    <span x-text="matchCount === 1 ? '1 match' : matchCount + ' matches'"></span>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10" x-show="matchCount > 0" x-cloak>
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Match details</flux:heading>

                <div class="grid gap-3">
                    <template x-for="(m, i) in matches.slice(0, 50)" :key="i">
                        <div class="rounded-md border border-black/10 p-4 dark:border-white/10">
                            <div class="mb-2 flex items-center gap-3 text-sm">
                                <span class="rounded bg-amber-500/10 px-2 py-0.5 font-mono font-semibold text-amber-700 dark:text-amber-300">
                                    Match <span x-text="i + 1"></span>
                                </span>
                                <span class="font-mono opacity-60">index: <span x-text="m.index"></span></span>
                                <span class="font-mono opacity-60">length: <span x-text="m.length"></span></span>
                            </div>
                            <div class="break-all rounded bg-zinc-50 p-2 font-mono text-sm dark:bg-zinc-900" x-text="m.match || '(empty)'"></div>

                            <template x-if="m.groups.length > 0">
                                <div class="mt-3">
                                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide opacity-60">Capture groups</div>
                                    <div class="grid gap-2">
                                        <template x-for="(g, gi) in m.groups" :key="gi">
                                            <div class="grid grid-cols-[auto_1fr] items-center gap-3">
                                                <span class="rounded bg-blue-500/10 px-2 py-0.5 font-mono text-xs text-blue-700 dark:text-blue-300">
                                                    $<span x-text="gi + 1"></span>
                                                </span>
                                                <span class="break-all rounded bg-zinc-50 p-2 font-mono text-sm dark:bg-zinc-900" x-text="g ?? '(undefined)'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <p x-show="matchCount > 50" x-cloak class="text-sm opacity-60">
                        Showing the first 50 of <span x-text="matchCount"></span> matches.
                    </p>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-black/10 pb-4 dark:border-white/10">
                    <flux:heading size="xl">4. Replace</flux:heading>
                    <flux:checkbox x-model="replaceMode" label="Enable replace" />
                </div>

                <template x-if="replaceMode">
                    <div class="grid gap-4">
                        <flux:input
                            x-model="replacement"
                            label="Replacement"
                            description="Use $1, $2, … to reference capture groups."
                            placeholder="hello $1"
                            class="font-mono"
                        />

                        <div>
                            <flux:label>Result</flux:label>
                            <div class="mt-2 min-h-[5rem] whitespace-pre-wrap rounded-md border border-black/10 bg-zinc-50 p-4 font-mono text-sm dark:border-white/10 dark:bg-zinc-900"
                                x-text="replaceResult || '(empty)'"></div>
                        </div>
                    </div>
                </template>
                <template x-if="!replaceMode">
                    <flux:text class="opacity-60">Turn on replace to substitute matched text.</flux:text>
                </template>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-2" size="xl">Share</flux:heading>
                <flux:subheading class="mb-4">
                    The URL below carries your pattern, flags, and test string.
                </flux:subheading>
                <p x-show="urlTooLong" x-cloak class="mb-4 text-sm text-amber-600 dark:text-amber-400">
                    Test string is too long to include in the URL.
                </p>
                <flux:input type="url" x-model="url" readonly copyable label="Share URL" />
            </div>
        </div>
    </div>
</x-layouts.app>
