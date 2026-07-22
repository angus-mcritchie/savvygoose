<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="formatConverter" x-on:keydown.window.escape="clear()">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.arrow-path-rounded-square class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Format Converter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Convert between JSON, YAML, CSV &amp; XML.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 grid items-end gap-4 lg:grid-cols-[1fr_auto_1fr_auto]">
                    <flux:field>
                        <flux:label>From</flux:label>
                        <flux:select x-model="from">
                            <flux:select.option value="json">JSON</flux:select.option>
                            <flux:select.option value="yaml">YAML</flux:select.option>
                            <flux:select.option value="csv">CSV</flux:select.option>
                            <flux:select.option value="xml">XML</flux:select.option>
                        </flux:select>
                    </flux:field>

                    <flux:button
                        x-on:click="swapDirection()"
                        icon="arrows-right-left"
                        variant="ghost"
                        aria-label="Swap source and target"
                    />

                    <flux:field>
                        <flux:label>To</flux:label>
                        <flux:select x-model="to">
                            <flux:select.option value="json">JSON</flux:select.option>
                            <flux:select.option value="yaml">YAML</flux:select.option>
                            <flux:select.option value="csv">CSV</flux:select.option>
                            <flux:select.option value="xml">XML</flux:select.option>
                        </flux:select>
                    </flux:field>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <flux:button x-on:click="loadSample()" icon="sparkles" variant="ghost">
                            Sample
                        </flux:button>
                        <flux:button x-on:click="clear()" x-bind:disabled="!input" icon="trash" variant="filled">
                            Clear
                        </flux:button>
                    </div>
                </div>

                <div class="mb-6 flex flex-wrap items-end gap-4 border-t border-black/10 pt-6 dark:border-white/10">
                    <flux:field>
                        <flux:label>Indent</flux:label>
                        <flux:select x-model="indent" size="sm" x-bind:disabled="!indentApplies">
                            <flux:select.option value="2">2 spaces</flux:select.option>
                            <flux:select.option value="4">4 spaces</flux:select.option>
                            <flux:select.option value="tab">Tab</flux:select.option>
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>CSV delimiter</flux:label>
                        <flux:select x-model="delimiter" size="sm" x-bind:disabled="!csvActive">
                            <flux:select.option value="comma">Comma ,</flux:select.option>
                            <flux:select.option value="tab">Tab</flux:select.option>
                            <flux:select.option value="semicolon">Semicolon ;</flux:select.option>
                            <flux:select.option value="pipe">Pipe |</flux:select.option>
                        </flux:select>
                    </flux:field>

                    <flux:input x-model="rootName" label="XML root element" size="sm" placeholder="root" x-bind:disabled="!xmlTargetActive" />
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <flux:label>
                                Input
                                <span class="ml-1 text-xs font-normal opacity-60" x-text="fromLabel"></span>
                            </flux:label>
                            <flux:text size="xs" class="opacity-60" x-show="input" x-cloak>
                                <span x-text="inputBytes.toLocaleString()"></span> bytes
                            </flux:text>
                        </div>
                        <flux:textarea
                            name="input"
                            x-model="input"
                            x-bind:placeholder="`Paste ${fromLabel} here…`"
                            rows="16"
                            class="font-mono"
                        />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <flux:label>
                                Output
                                <span class="ml-1 text-xs font-normal opacity-60" x-text="toLabel"></span>
                            </flux:label>
                            <div class="flex items-center gap-3">
                                <flux:text size="xs" class="opacity-60" x-show="output" x-cloak>
                                    <span x-text="outputBytes.toLocaleString()"></span> bytes
                                </flux:text>
                                <x-copy-button
                                    value="output"
                                    flash="'format-output'"
                                    icon="document-duplicate"
                                    size="xs"
                                    x-bind:disabled="!output"
                                />
                                <flux:button
                                    x-on:click="$download(output, downloadFilename, downloadMime)"
                                    x-bind:disabled="!output"
                                    icon="arrow-down-tray"
                                    size="xs"
                                    variant="ghost"
                                >
                                    Download
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
                            x-bind:placeholder="`Converted ${toLabel} will appear here.`"
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
                </div>

                <div
                    x-show="!error && input.trim() && output"
                    x-cloak
                    class="mt-6 inline-flex items-center gap-2 rounded-md border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700 dark:text-emerald-300"
                >
                    <flux:icon.check-circle class="size-4" />
                    <span x-text="`Valid ${fromLabel} → ${toLabel}`"></span>
                </div>
            </div>

            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="The URL below carries your input and conversion settings. Anyone who opens it sees the same conversion."
                tooLongMessage="Input is too long to include in the URL. Copy the converted output to share instead."
            />
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
