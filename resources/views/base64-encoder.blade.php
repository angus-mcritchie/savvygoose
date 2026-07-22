<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="base64Encoder" x-on:keydown.window.escape="clear()">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.arrows-right-left class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Base64 Encoder</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Encode and decode Base64. Text or files, all in your browser.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <flux:radio.group x-model="mode" variant="segmented" size="sm">
                        <flux:radio value="text" label="Text" />
                        <flux:radio value="file" label="File" />
                    </flux:radio.group>
                </div>

                <template x-if="mode === 'text'">
                    <div>
                        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                            <flux:radio.group x-model="direction" variant="segmented" size="sm">
                                <flux:radio value="encode" label="Encode" />
                                <flux:radio value="decode" label="Decode" />
                            </flux:radio.group>

                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-1">
                                    <flux:checkbox x-model="urlSafe" label="URL-safe" />
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="What is URL-safe Base64?" />
                                        <flux:popover class="max-w-sm">
                                            <flux:heading size="sm">URL-safe Base64</flux:heading>
                                            <p class="mt-2 text-sm">Standard Base64 uses <code class="font-mono">+</code>, <code class="font-mono">/</code>, and <code class="font-mono">=</code>, all of which need percent-encoding inside URLs.</p>
                                            <flux:separator class="my-3" />
                                            <p class="text-sm">URL-safe Base64 (RFC 4648 §5) swaps:</p>
                                            <ul class="mt-2 space-y-1 font-mono text-sm">
                                                <li>+ → -</li>
                                                <li>/ → _</li>
                                                <li>= → (stripped)</li>
                                            </ul>
                                            <flux:separator class="my-3" />
                                            <p class="text-sm">Used by JWTs, OAuth tokens, and anywhere Base64 ends up in a URL or filename.</p>
                                        </flux:popover>
                                    </flux:dropdown>
                                </div>
                                <flux:button x-on:click="swap()" x-bind:disabled="!output" icon="arrows-right-left" size="sm">
                                    Swap
                                </flux:button>
                                <flux:button x-on:click="clear()" x-bind:disabled="!input" icon="trash" size="sm" variant="filled">
                                    Clear
                                </flux:button>
                            </div>
                        </div>

                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="grid gap-2">
                                <flux:label x-text="inputLabel">Input</flux:label>
                                <flux:textarea
                                    name="input"
                                    x-model="input"
                                    x-bind:placeholder="inputPlaceholder"
                                    rows="14"
                                    class="font-mono"
                                />
                            </div>

                            <div class="grid gap-2">
                                <div class="flex items-center justify-between">
                                    <flux:label x-text="outputLabel">Output</flux:label>
                                    <div class="flex gap-2">
                                        <flux:button
                                            x-show="direction === 'decode' && (output || binaryDecode)"
                                            x-cloak
                                            x-on:click="downloadDecoded()"
                                            icon="arrow-down-tray"
                                            size="xs"
                                            variant="ghost"
                                        >
                                            Download as file
                                        </flux:button>
                                        <x-copy-button
                                            value="output"
                                            flash="'b64-output'"
                                            icon="document-duplicate"
                                            size="xs"
                                            x-bind:disabled="!output"
                                        />
                                    </div>
                                </div>
                                <flux:textarea
                                    name="output"
                                    x-bind:value="output"
                                    readonly
                                    rows="14"
                                    class="font-mono"
                                />
                                <p
                                    x-show="error"
                                    x-cloak
                                    x-text="error"
                                    class="text-sm text-red-600 dark:text-red-400"
                                ></p>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="mode === 'file'">
                    <div class="grid gap-6">
                        <x-file-picker
                            binding="file"
                            on-change="onFileSelected"
                            on-clear="clearFile"
                            error="fileError"
                            helper="Files up to 25 MB. Nothing leaves your browser."
                        />

                        <template x-if="fileResult || fileBusy">
                            <div class="grid gap-2">
                                <div class="flex items-center justify-between">
                                    <flux:label>Base64</flux:label>
                                    <x-copy-button
                                        value="fileResult"
                                        flash="'b64-file'"
                                        icon="document-duplicate"
                                        size="xs"
                                        x-bind:disabled="!fileResult"
                                    />
                                </div>
                                <flux:textarea
                                    x-bind:value="fileBusy ? 'Encoding…' : fileResult"
                                    readonly
                                    rows="14"
                                    class="font-mono"
                                />
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <template x-if="mode === 'text'">
                <x-share-field
                    class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                    subheading="The URL below carries the direction, URL-safe flag, and your input. File mode does not share."
                    tooLongMessage="Input is too long to include in the URL."
                />
            </template>
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
