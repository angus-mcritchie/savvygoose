<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="markdownConverter">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.code-bracket-square class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Markdown Converter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Convert between Markdown and HTML.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <flux:radio.group x-model="direction" variant="segmented" size="sm">
                        <flux:radio value="md-to-html" label="Markdown → HTML" />
                        <flux:radio value="html-to-md" label="HTML → Markdown" />
                    </flux:radio.group>

                    <div class="flex gap-2">
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
                        <div class="flex items-center justify-between">
                            <flux:label x-text="inputLabel">Input</flux:label>
                            <flux:button x-on:click="copyInput()" x-bind:disabled="!input" icon="document-duplicate" size="xs" variant="ghost">
                                <span x-text="copiedInput ? 'Copied!' : 'Copy'">Copy</span>
                            </flux:button>
                        </div>
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
                            <flux:button x-on:click="copyOutput()" x-bind:disabled="!output" icon="document-duplicate" size="xs" variant="ghost">
                                <span x-text="copiedOutput ? 'Copied!' : 'Copy'">Copy</span>
                            </flux:button>
                        </div>
                        <flux:textarea
                            name="output"
                            x-bind:value="output"
                            x-on:beforeinput.prevent="$dispatch('modal-show', { name: 'swap-confirm' })"
                            rows="14"
                            class="font-mono"
                        />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Preview</flux:heading>
                <template x-if="preview">
                    <div class="prose max-w-none dark:prose-invert" x-html="preview"></div>
                </template>
                <template x-if="!preview">
                    <div class="flex flex-col items-center justify-center gap-2 py-10 text-center text-zinc-500 dark:text-zinc-400">
                        <flux:icon.eye class="size-8 opacity-50" />
                        <flux:text>Type something on the left to see a rendered preview here.</flux:text>
                    </div>
                </template>
            </div>
        </div>

        <flux:modal name="swap-confirm" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Swap to edit this side?</flux:heading>
                    <flux:text class="mt-2">
                        The output is generated from the input. Swap directions so the current output becomes the new input — then you can edit it freely.
                    </flux:text>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button
                        variant="primary"
                        icon="arrows-right-left"
                        x-on:click="swap(); $dispatch('modal-close', { name: 'swap-confirm' })"
                    >
                        Swap &amp; edit
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</x-layouts.app>
