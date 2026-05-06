<x-layouts.app>

    <div class="mx-auto max-w-[1100px]" x-data="uuidGenerator">

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon name="identification" class="size-[96px] text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">UUID Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Generate v4 (random) or v7 (time-ordered) UUIDs in bulk.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">
                    Settings
                </flux:heading>

                <div class="grid gap-5">
                    <flux:radio.group x-model="version" label="Version">
                        <flux:radio value="v4" label="v4" description="Random — universally supported." />
                        <flux:radio value="v7" label="v7" description="Time-ordered — friendlier to databases." />
                    </flux:radio.group>

                    <flux:input
                        type="number"
                        min="1"
                        max="1000"
                        step="1"
                        x-model.number="count"
                        label="How many"
                        description="1 to 1,000 at a time."
                    />

                    <div class="grid gap-3">
                        <flux:label>Format</flux:label>
                        <flux:checkbox x-model="hyphens" label="Hyphens" />
                        <flux:checkbox x-model="uppercase" label="Uppercase" />
                        <flux:checkbox x-model="braces" label="Wrap in braces { }" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-black/10 pb-4 dark:border-white/10">
                    <flux:heading size="xl">
                        Output
                        <span class="opacity-60">(<span x-text="uuids.length"></span>)</span>
                    </flux:heading>
                    <div class="flex gap-2">
                        <flux:button x-on:click="generate" icon="arrow-path" size="sm">Regenerate</flux:button>
                        <flux:button
                            x-on:click="copyAll"
                            icon="document-duplicate"
                            size="sm"
                            variant="primary"
                            x-bind:disabled="!uuids.length"
                        >
                            <span x-text="copied ? 'Copied!' : 'Copy all'">Copy all</span>
                        </flux:button>
                        <flux:button
                            x-on:click="download"
                            icon="arrow-down-tray"
                            size="sm"
                            x-bind:disabled="!uuids.length"
                        >
                            .txt
                        </flux:button>
                    </div>
                </div>

                <div class="max-h-[480px] overflow-y-auto rounded-md border border-black/10 dark:border-white/10">
                    <ul class="divide-y divide-black/5 font-mono text-sm dark:divide-white/5">
                        <template x-for="(u, i) in uuids" :key="i + u">
                            <li class="grid grid-cols-[1fr_auto] items-center gap-3 px-4 py-2 hover:bg-black/5 dark:hover:bg-white/5">
                                <span class="truncate" x-text="format(u)"></span>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs opacity-60 hover:bg-black/5 hover:opacity-100 dark:hover:bg-white/10"
                                    x-on:click="copyOne(i)"
                                    x-text="copiedIndex === i ? 'Copied' : 'Copy'"
                                >Copy</button>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 lg:col-span-2 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Share</flux:heading>
                <flux:subheading class="mb-4">
                    The URL below carries every option — open it to generate fresh UUIDs with the same settings.
                </flux:subheading>
                <flux:input type="url" x-model="url" readonly copyable label="Share URL" />
            </div>
        </div>
    </div>
</x-layouts.app>
