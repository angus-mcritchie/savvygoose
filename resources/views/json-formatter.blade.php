<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="jsonFormatter">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.code-bracket class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">JSON Formatter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Pretty-print, minify, and validate JSON.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-1">
                        <flux:radio.group x-model="mode" variant="segmented" size="sm">
                            <flux:radio value="pretty" label="Pretty" />
                            <flux:radio value="minified" label="Minified" />
                        </flux:radio.group>
                        <flux:dropdown position="bottom" align="start">
                            <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="Pretty vs minified" />
                            <flux:popover class="max-w-sm">
                                <flux:heading size="sm">Pretty vs minified</flux:heading>
                                <p class="mt-2 text-sm"><strong>Pretty</strong>: adds indentation &amp; newlines so a human can read it. Use for debugging, configs, or commit-friendly fixtures.</p>
                                <flux:separator class="my-3" />
                                <p class="text-sm"><strong>Minified</strong>: strips every byte of whitespace. Use over the wire; it's smaller, gzips well, and parses identically.</p>
                                <flux:separator class="my-3" />
                                <p class="text-sm">Both forms are byte-for-byte equivalent to a JSON parser.</p>
                            </flux:popover>
                        </flux:dropdown>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <flux:select x-model="indent" size="sm" x-bind:disabled="mode === 'minified'">
                            <flux:select.option value="2">2 spaces</flux:select.option>
                            <flux:select.option value="4">4 spaces</flux:select.option>
                            <flux:select.option value="tab">Tab</flux:select.option>
                        </flux:select>
                        <flux:button x-on:click="loadSample()" icon="sparkles" size="sm" variant="ghost">
                            Sample
                        </flux:button>
                        <flux:button x-on:click="clear()" x-bind:disabled="!input" icon="trash" size="sm" variant="filled">
                            Clear
                        </flux:button>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <flux:label>Input</flux:label>
                            <flux:text size="xs" class="opacity-60" x-show="input" x-cloak>
                                <span x-text="inputBytes.toLocaleString()"></span> bytes
                            </flux:text>
                        </div>
                        <flux:textarea
                            name="input"
                            x-model="input"
                            placeholder='{ "hello": "world" }'
                            rows="16"
                            class="font-mono"
                        />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <flux:label>Output</flux:label>
                            <div class="flex items-center gap-3">
                                <flux:text size="xs" class="opacity-60" x-show="output" x-cloak>
                                    <span x-text="outputBytes.toLocaleString()"></span> bytes
                                    <template x-if="mode === 'minified' && savings > 0">
                                        <span> · <span x-text="savings"></span>% smaller</span>
                                    </template>
                                </flux:text>
                                <flux:button x-on:click="copyOutput()" x-bind:disabled="!output" icon="document-duplicate" size="xs" variant="ghost">
                                    <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                                </flux:button>
                                <flux:button x-on:click="swapToOutput()" x-bind:disabled="!output" icon="arrow-left" size="xs" variant="ghost">
                                    Use as input
                                </flux:button>
                            </div>
                        </div>
                        <flux:textarea
                            name="output"
                            x-bind:value="output"
                            readonly
                            placeholder="Formatted JSON will appear here."
                            rows="16"
                            class="font-mono"
                        />
                    </div>
                </div>

                <div
                    x-show="error"
                    x-cloak
                    class="mt-6 rounded-md border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-700 dark:text-red-300"
                >
                    <div class="font-semibold" x-text="error?.message"></div>
                    <template x-if="error?.line">
                        <div class="mt-2">
                            <div class="opacity-80">Line <span x-text="error.line"></span>, column <span x-text="error.col"></span>:</div>
                            <pre class="mt-1 overflow-x-auto rounded bg-red-950/40 p-2 font-mono text-xs"><code x-text="error.snippet || ''"></code></pre>
                        </div>
                    </template>
                </div>

                <div
                    x-show="!error && input.trim() && output"
                    x-cloak
                    class="mt-6 inline-flex items-center gap-2 rounded-md border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700 dark:text-emerald-300"
                >
                    <flux:icon.check-circle class="size-4" />
                    Valid JSON
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-2" size="xl">Share</flux:heading>
                <flux:subheading class="mb-4">
                    The URL below carries your JSON and settings. Anyone who opens it sees the same input.
                </flux:subheading>
                <p x-show="urlTooLong" x-cloak class="mb-4 text-sm text-amber-600 dark:text-amber-400">
                    Input is too long to include in the URL. Copy the formatted output to share instead.
                </p>
                <flux:input type="url" x-model="url" readonly copyable label="Share URL" />
            </div>
        </div>
    </div>
</x-layouts.app>
