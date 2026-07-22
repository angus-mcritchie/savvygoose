<x-layouts.app>

    <div
        class="mx-auto max-w-[1000px]"
        x-data="passwordGenerator"
        x-on:keydown.window.cmd.enter.prevent="generate()"
        x-on:keydown.window.ctrl.enter.prevent="generate()"
    >

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon name="lock-closed" class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Password Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Strong, random passwords, generated entirely in your browser.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">
                    Your password
                </flux:heading>

                <div class="mb-4 grid grid-cols-[1fr_auto] gap-2">
                    <flux:input
                        x-ref="output"
                        x-model="password"
                        readonly
                        class="!font-mono"
                        x-bind:placeholder="!hasCharset ? 'Pick at least one character set' : ''"
                    />
                    <flux:button x-on:click="generate" icon="arrow-path" x-bind:disabled="!hasCharset">
                        Regenerate
                    </flux:button>
                </div>

                <x-copy-button
                    class="mb-6"
                    variant="primary"
                    value="password"
                    flash="'pwd'"
                    label="Copy password"
                    copiedLabel="Copied!"
                    icon="document-duplicate"
                    x-bind:disabled="!password"
                />

                <div x-show="hasCharset" x-cloak>
                    <div class="mb-2 flex items-baseline justify-between">
                        <div class="flex items-center gap-1">
                            <flux:subheading>Strength</flux:subheading>
                            <flux:dropdown position="bottom" align="start">
                                <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="What is entropy?" />
                                <flux:popover class="max-w-sm">
                                    <flux:heading size="sm">Bits of entropy</flux:heading>
                                    <p class="mt-2 text-sm">Each bit doubles the number of possible passwords an attacker must try.</p>
                                    <p class="mt-2 font-mono text-sm">entropy = length × log₂(charset size)</p>
                                    <flux:separator class="my-3" />
                                    <ul class="space-y-1 text-sm">
                                        <li><strong>&lt; 40</strong>: weak; cracked in seconds offline.</li>
                                        <li><strong>40–60</strong>: fair; OK for low-stakes accounts.</li>
                                        <li><strong>60–80</strong>: strong; resists offline attacks for years.</li>
                                        <li><strong>80+</strong>: very strong; future-proof.</li>
                                    </ul>
                                    <flux:separator class="my-3" />
                                    <p class="text-sm">Assumes a uniformly random password. Reused or dictionary words have far less effective entropy.</p>
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
                                class="h-2 rounded-full"
                                :class="i <= strength.score ? strength.tone : 'bg-black/10 dark:bg-white/10'"
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
                        <flux:label>Length</flux:label>
                        <flux:subheading class="font-mono tabular-nums" x-text="length"></flux:subheading>
                    </div>
                    <flux:slider min="4" max="128" step="1" x-model.number="length" />
                    <div class="mt-2 flex justify-between text-xs opacity-60">
                        <span>4</span>
                        <span>128</span>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-3">
                        <flux:checkbox x-model="lower" label="Lowercase (a-z)" />
                        <flux:checkbox x-model="upper" label="Uppercase (A-Z)" />
                        <flux:checkbox x-model="digits" label="Digits (0-9)" />
                        <flux:checkbox x-model="symbols" label="Symbols (!@#…)" />
                    </div>
                    <div class="grid gap-3">
                        <flux:checkbox
                            x-model="excludeSimilar"
                            label="Exclude similar"
                            description="Drops 1, l, I, 0, O, o."
                        />
                        <flux:checkbox
                            x-model="excludeAmbiguous"
                            label="Exclude ambiguous symbols"
                            description="Drops { } [ ] ( ) / \ ' &quot; ` ~ , ; : . &lt; &gt;"
                        />
                    </div>
                </div>

                <div
                    x-show="!hasCharset"
                    x-cloak
                    class="mt-6 rounded-md border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-700 dark:text-amber-300"
                >
                    Pick at least one character set.
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Share</flux:heading>
                <x-share-field
                    :heading="false"
                    subheading="The URL below carries your settings, not your password. Open it anywhere to generate a fresh password with the same options."
                />
            </div>
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
