<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="hashGenerator">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.finger-print class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Hash Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        MD5, SHA-1, SHA-256 and SHA-512, computed in your browser.
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
                    <div class="grid gap-4">
                        <div class="flex items-center justify-between">
                            <flux:label>Text</flux:label>
                            <flux:button x-on:click="clearText()" x-bind:disabled="!text" icon="trash" size="sm" variant="filled">
                                Clear
                            </flux:button>
                        </div>
                        <flux:textarea
                            name="text"
                            x-model="text"
                            placeholder="Type or paste text. Hashes update as you type."
                            rows="8"
                            class="font-mono"
                        />
                    </div>
                </template>

                <template x-if="mode === 'file'">
                    <div class="grid gap-4">
                        <x-file-picker
                            binding="file"
                            on-change="onFileSelected"
                            on-clear="clearFile"
                            error="fileError"
                            helper="Files up to 100 MB. Nothing leaves your browser."
                        />

                        <template x-if="busy">
                            <div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                    <div
                                        class="h-full bg-zinc-900 transition-[width] dark:bg-white"
                                        :style="`width: ${Math.round(progress * 100)}%`"
                                    ></div>
                                </div>
                                <p class="mt-2 text-xs opacity-60">
                                    Hashing… <span x-text="Math.round(progress * 100) + '%'"></span>
                                </p>
                            </div>
                        </template>

                        <p
                            x-show="fileError"
                            x-cloak
                            x-text="fileError"
                            class="text-sm text-red-600 dark:text-red-400"
                        ></p>
                    </div>
                </template>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex items-center justify-between gap-2 border-b border-black/10 pb-4 dark:border-white/10">
                    <flux:heading size="lg">Hashes</flux:heading>
                    <div class="flex items-center gap-2">
                        <flux:button
                            x-on:click="$download(algos.map(a => `${a.label}: ${hashes[a.key]}`).join('\n') + '\n', 'hashes.txt')"
                            x-bind:disabled="!hashes.md5"
                            icon="arrow-down-tray"
                            size="sm"
                            variant="ghost"
                        >
                            .txt
                        </flux:button>
                        <flux:dropdown position="bottom" align="end">
                        <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="Which hash should I use?" />
                        <flux:popover class="max-w-sm">
                            <flux:heading size="sm">Which one should I use?</flux:heading>
                            <ul class="mt-2 space-y-2 text-sm">
                                <li><strong>MD5</strong>: 128-bit. Fast but broken; only OK for non-security checksums.</li>
                                <li><strong>SHA-1</strong>: 160-bit. Also broken for collisions; avoid for new work.</li>
                                <li><strong>SHA-256</strong>: 256-bit. Safe default for integrity checks &amp; signatures.</li>
                                <li><strong>SHA-512</strong>: 512-bit. Same family, larger digest, often faster on 64-bit CPUs.</li>
                            </ul>
                            <flux:separator class="my-3" />
                            <p class="text-sm">For passwords, use bcrypt/argon2. These are too fast to be safe alone.</p>
                        </flux:popover>
                    </flux:dropdown>
                    </div>
                </div>
                <div class="grid gap-4">
                    <template x-for="algo in algos" :key="algo.key">
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <flux:label x-text="algo.label">Hash</flux:label>
                                <x-copy-button
                                    value="hashes[algo.key]"
                                    flash="'hash-' + algo.key"
                                    icon="document-duplicate"
                                    size="xs"
                                    x-bind:disabled="!hashes[algo.key]"
                                />
                            </div>
                            <flux:input
                                x-bind:value="hashes[algo.key]"
                                readonly
                                placeholder=""
                                class="!font-mono"
                            />
                        </div>
                    </template>
                </div>
                <p class="mt-6 text-xs opacity-60">
                    MD5 and SHA-1 are broken for collision resistance. Use SHA-256 or SHA-512 for security-sensitive checks.
                </p>
            </div>

            <template x-if="mode === 'text'">
                <x-share-field
                    class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                    subheading="The URL below carries your input. File mode does not share."
                    tooLongMessage="Input is too long to include in the URL."
                />
            </template>
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
