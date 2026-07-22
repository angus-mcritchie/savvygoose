<x-layouts.app>
    <div class="mx-auto max-w-[1000px]" x-data="faviconGenerator" x-on:keydown.window.escape="clearFile()">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.sparkles class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Favicon Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Turn an image into favicons and a multi-size .ico, in your browser.
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
                    helper="A square PNG or SVG works best. Up to 10 MB. Nothing leaves your browser."
                />
            </div>

            <template x-if="ready">
                <div class="grid gap-6">
                    <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                        <flux:heading size="lg" class="mb-6">Preview &amp; download</flux:heading>
                        <div class="flex flex-wrap items-end justify-center gap-6">
                            <template x-for="size in previewSizes" :key="size">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex items-center justify-center rounded-md border border-black/10 bg-[repeating-conic-gradient(#e5e5e5_0_25%,transparent_0_50%)] bg-[length:12px_12px] p-2 dark:border-white/10">
                                        <img :src="previews[size]" :width="Math.min(size, 96)" :height="Math.min(size, 96)" :alt="size + ' pixel favicon'" class="block" />
                                    </div>
                                    <span class="font-mono text-xs text-zinc-500 dark:text-zinc-400" x-text="size + '×' + size"></span>
                                    <flux:button size="xs" variant="ghost" icon="arrow-down-tray" x-on:click="downloadPng(size)">PNG</flux:button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <flux:heading size="lg">favicon.ico</flux:heading>
                                <flux:subheading>A single multi-size icon (16, 32, and 48 px) for the classic /favicon.ico.</flux:subheading>
                            </div>
                            <flux:button variant="primary" icon="arrow-down-tray" x-on:click="downloadIco()">Download .ico</flux:button>
                        </div>
                    </div>

                    <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <div>
                                <flux:heading size="lg">HTML</flux:heading>
                                <flux:subheading>Drop this into your page &lt;head&gt; after placing the files at your site root.</flux:subheading>
                            </div>
                            <x-copy-button value="linkSnippet" flash="'favicon-html'" size="xs" />
                        </div>
                        <flux:textarea x-bind:value="linkSnippet" readonly rows="3" class="font-mono text-xs" />
                    </div>
                </div>
            </template>
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
