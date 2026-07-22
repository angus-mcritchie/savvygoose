<x-layouts.app>
    <div class="mx-auto max-w-[1000px]" x-data="imageToBase64" x-on:keydown.window.escape="clearFile()">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.photo class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Image to Base64</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Turn an image into a Base64 data URI, all in your browser.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <x-file-picker
                    accept="image/*"
                    binding="file"
                    on-change="onFileSelected"
                    on-clear="clearFile"
                    error="error"
                    helper="PNG, JPEG, GIF, SVG, or WebP up to 10 MB. Nothing leaves your browser."
                />

                <template x-if="dataUri">
                    <div class="mt-6 grid gap-6">
                        <div class="grid gap-4 sm:grid-cols-[auto_1fr] sm:items-center">
                            <div class="flex items-center justify-center rounded-md border border-black/10 bg-[repeating-conic-gradient(#e5e5e5_0_25%,transparent_0_50%)] bg-[length:16px_16px] p-4 dark:border-white/10">
                                <img :src="dataUri" alt="Preview" class="max-h-32 max-w-[160px] object-contain" />
                            </div>
                            <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                                <dt class="text-zinc-500 dark:text-zinc-400">File</dt>
                                <dd class="truncate font-medium" x-text="fileName"></dd>
                                <dt class="text-zinc-500 dark:text-zinc-400">Type</dt>
                                <dd class="font-mono" x-text="fileType"></dd>
                                <dt class="text-zinc-500 dark:text-zinc-400">Original</dt>
                                <dd class="font-mono tabular-nums" x-text="formatBytes(fileSize)"></dd>
                                <dt class="text-zinc-500 dark:text-zinc-400">Base64</dt>
                                <dd class="font-mono tabular-nums" x-text="formatBytes(encodedSize)"></dd>
                            </dl>
                        </div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Base64 adds roughly a third to the file size. It's handy for inlining small icons; for large images a regular file is lighter.
                        </p>
                    </div>
                </template>
            </div>

            <template x-if="dataUri">
                <div class="grid gap-6">
                    @foreach ([
                        ['label' => 'Data URI', 'value' => 'dataUri', 'flash' => 'i2b-uri', 'file' => 'image.txt'],
                        ['label' => 'HTML &lt;img&gt; tag', 'value' => 'imgTag', 'flash' => 'i2b-img', 'file' => 'image.html'],
                        ['label' => 'CSS background', 'value' => 'cssBackground', 'flash' => 'i2b-css', 'file' => 'image.css'],
                    ] as $out)
                        <div class="rounded-lg border border-black/10 p-6 dark:border-white/10">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <flux:label>{!! $out['label'] !!}</flux:label>
                                <div class="flex gap-2">
                                    <x-copy-button value="{{ $out['value'] }}" flash="'{{ $out['flash'] }}'" size="xs" />
                                    <flux:button
                                        x-on:click="$download({{ $out['value'] }}, '{{ $out['file'] }}', 'text/plain')"
                                        icon="arrow-down-tray"
                                        size="xs"
                                        variant="ghost"
                                    >
                                        Download
                                    </flux:button>
                                </div>
                            </div>
                            <flux:textarea x-bind:value="{{ $out['value'] }}" readonly rows="4" class="font-mono text-xs" />
                        </div>
                    @endforeach
                </div>
            </template>
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
