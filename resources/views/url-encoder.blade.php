<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="urlEncoder">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.link class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">URL Encoder</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Percent-encode and decode text for use in URLs.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <flux:radio.group x-model="direction" variant="segmented" size="sm">
                        <flux:radio value="encode" label="Encode" />
                        <flux:radio value="decode" label="Decode" />
                    </flux:radio.group>

                    <div class="flex flex-wrap items-center gap-3">
                        <flux:button x-on:click="swap()" x-bind:disabled="!output" icon="arrows-right-left" size="sm">
                            Swap
                        </flux:button>
                        <flux:button x-on:click="clear()" x-bind:disabled="!input" icon="trash" size="sm" variant="filled">
                            Clear
                        </flux:button>
                    </div>
                </div>

                <div class="mb-6">
                    <flux:radio.group x-model="variant" label="Mode" size="sm">
                        <flux:radio value="component" label="Component" description="encodeURIComponent — escapes everything but unreserved chars. Use for query values & path segments." />
                        <flux:radio value="uri" label="Whole URI" description="encodeURI — leaves URL structure (: / ? & # =) intact. Use for full URLs." />
                    </flux:radio.group>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="grid gap-2">
                        <flux:label x-text="inputLabel">Input</flux:label>
                        <flux:textarea
                            name="input"
                            x-model="input"
                            x-bind:placeholder="inputPlaceholder"
                            rows="12"
                            class="font-mono"
                        />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <flux:label x-text="outputLabel">Output</flux:label>
                            <flux:button x-on:click="copy()" x-bind:disabled="!output" icon="document-duplicate" size="xs" variant="ghost">
                                <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                            </flux:button>
                        </div>
                        <flux:textarea
                            name="output"
                            x-bind:value="output"
                            readonly
                            rows="12"
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
        </div>
    </div>
</x-layouts.app>
