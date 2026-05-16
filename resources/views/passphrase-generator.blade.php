<x-layouts.app>

    <div
        class="mx-auto max-w-[1000px]"
        x-data="passphraseGenerator"
        x-on:keydown.window.cmd.enter.prevent="generate()"
        x-on:keydown.window.ctrl.enter.prevent="generate()"
    >

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon name="key" class="size-[96px] text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Passphrase Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Memorable, random passphrases built from real words.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">
                    Your passphrase
                </flux:heading>

                <div class="mb-4 grid grid-cols-[1fr_auto] gap-2">
                    <flux:input
                        x-ref="output"
                        x-model="passphrase"
                        readonly
                        class="!font-mono"
                    />
                    <flux:button x-on:click="generate" icon="arrow-path">
                        Regenerate
                    </flux:button>
                </div>

                <x-copy-button
                    class="mb-6"
                    variant="primary"
                    value="passphrase"
                    flash="'passphrase'"
                    label="Copy passphrase"
                    copiedLabel="Copied!"
                    icon="document-duplicate"
                    x-bind:disabled="!passphrase"
                />

                <div>
                    <div class="mb-2 flex items-baseline justify-between">
                        <div class="flex items-center gap-1">
                            <flux:subheading>Strength</flux:subheading>
                            <flux:dropdown position="bottom" align="start">
                                <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="What is entropy?" />
                                <flux:popover class="max-w-sm">
                                    <flux:heading size="sm">Bits of entropy</flux:heading>
                                    <p class="mt-2 text-sm">Each bit doubles the number of possible passphrases an attacker must try.</p>
                                    <p class="mt-2 font-mono text-sm">entropy ≈ words × log₂(7776)</p>
                                    <flux:separator class="my-3" />
                                    <ul class="space-y-1 text-sm">
                                        <li><strong>&lt; 40</strong>: weak; cracked in seconds offline.</li>
                                        <li><strong>40–60</strong>: fair; OK for low-stakes accounts.</li>
                                        <li><strong>60–80</strong>: strong; resists offline attacks for years.</li>
                                        <li><strong>80+</strong>: very strong; future-proof.</li>
                                    </ul>
                                    <flux:separator class="my-3" />
                                    <p class="text-sm">Each EFF word adds ~12.9 bits. Five words clears 64 bits.</p>
                                </flux:popover>
                            </flux:dropdown>
                        </div>
                        <flux:subheading>
                            <span x-text="strength.label"></span>
                            <span class="opacity-60">·</span>
                            <span><span x-text="entropy"></span> bits of entropy</span>
                        </flux:subheading>
                    </div>
                    <div class="grid grid-cols-5 gap-1">
                        <template x-for="i in 5" :key="i">
                            <div
                                class="h-2 rounded-full bg-black/10 dark:bg-white/10"
                                :class="i <= strength.score ? strength.tone : ''"
                            ></div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">
                    Settings
                </flux:heading>

                <div class="mb-6">
                    <div class="mb-2 flex items-baseline justify-between">
                        <flux:label>Words</flux:label>
                        <flux:subheading class="font-mono tabular-nums" x-text="words"></flux:subheading>
                    </div>
                    <flux:slider min="2" max="12" step="1" x-model.number="words" />
                    <div class="mt-2 flex justify-between text-xs opacity-60">
                        <span>2</span>
                        <span>12</span>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <flux:select x-model="separator" label="Separator">
                            <flux:select.option value="space">Space</flux:select.option>
                            <flux:select.option value="dash">Dash (-)</flux:select.option>
                            <flux:select.option value="underscore">Underscore (_)</flux:select.option>
                            <flux:select.option value="dot">Dot (.)</flux:select.option>
                            <flux:select.option value="none">None</flux:select.option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:select x-model="capitalize" label="Capitalisation">
                            <flux:select.option value="none">all lowercase</flux:select.option>
                            <flux:select.option value="first">First Letter</flux:select.option>
                            <flux:select.option value="all">ALL CAPS</flux:select.option>
                            <flux:select.option value="random">rANdoM</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <div class="mt-6 grid gap-3">
                    <flux:checkbox
                        x-model="includeNumber"
                        label="Append a digit"
                        description="Adds a single 0-9 at the end. Useful for sites that demand a number."
                    />
                    <flux:checkbox
                        x-model="includeSymbol"
                        label="Append a symbol"
                        description="Adds one of ! @ # $ % ^ & * ? + = at the end."
                    />
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Share</flux:heading>
                <x-share-field
                    :heading="false"
                    subheading="The URL below carries your settings, not your passphrase. Open it anywhere to generate a fresh passphrase with the same options."
                />
            </div>
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
