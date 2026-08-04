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

                <div class="grid gap-4" x-show="mode === 'text'">
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

                <div class="grid gap-4" x-show="mode === 'file'" x-cloak>
                    <x-file-picker
                        binding="file"
                        on-change="onFileSelected"
                        on-clear="clearFile"
                        error="fileError"
                        helper="Files up to 100 MB. Nothing leaves your browser."
                    />

                    <div x-show="busy" x-cloak>
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

                    <p
                        x-show="fileError"
                        x-cloak
                        x-text="fileError"
                        class="text-sm text-red-600 dark:text-red-400"
                    ></p>
                </div>
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
                @php
                    // Rendered server-side rather than with x-for so the four rows occupy their
                    // final space before Alpine boots. Mirrors ALGOS in resources/js/data/hashGenerator.js.
                    $algos = ['md5' => 'MD5', 'sha1' => 'SHA-1', 'sha256' => 'SHA-256', 'sha512' => 'SHA-512'];
                @endphp
                <div class="grid gap-4">
                    @foreach ($algos as $key => $label)
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <flux:label>{{ $label }}</flux:label>
                                <x-copy-button
                                    value="hashes.{{ $key }}"
                                    flash="'hash-{{ $key }}'"
                                    icon="document-duplicate"
                                    size="xs"
                                    x-bind:disabled="!hashes.{{ $key }}"
                                />
                            </div>
                            <flux:input
                                x-bind:value="hashes.{{ $key }}"
                                readonly
                                placeholder=""
                                class="!font-mono"
                            />
                        </div>
                    @endforeach
                </div>
                <p class="mt-6 text-xs opacity-60">
                    MD5 and SHA-1 are broken for collision resistance. Use SHA-256 or SHA-512 for security-sensitive checks.
                </p>
            </div>

            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="The URL below carries your input. File mode does not share."
                tooLongMessage="Input is too long to include in the URL."
                x-show="mode === 'text'"
            />
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
