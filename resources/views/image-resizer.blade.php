<x-layouts.app>

    <div
        class="mx-auto max-w-[1200px]"
        x-data="imageResizer"
        x-on:paste.window="onPaste"
    >

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon name="photo" class="size-[96px] text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Image Resizer</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Resize, convert & build favicon packs — entirely in your browser.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">1. Choose an image</flux:heading>

                <div
                    class="grid place-items-center rounded-lg border-2 border-dashed px-6 py-10 text-center transition"
                    :class="dragging
                        ? 'border-zinc-900 bg-black/5 dark:border-white dark:bg-white/5'
                        : 'border-black/15 dark:border-white/15'"
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave.prevent="dragging = false"
                    x-on:drop.prevent="onDrop($event)"
                >
                    <flux:icon name="photo" class="mb-3 size-10 opacity-40" />
                    <flux:text class="mb-1">
                        <strong>Drop an image here</strong>, paste from your clipboard,
                    </flux:text>
                    <flux:text size="sm" class="mb-4 opacity-70">
                        or use the button below. PNG, JPEG, WebP, SVG, GIF — up to 20 MB.
                    </flux:text>
                    <input type="file" accept="image/*" x-ref="picker" x-on:change="onPick" class="hidden" />
                    <flux:button size="sm" x-on:click="$refs.picker.click()">Choose file</flux:button>
                </div>

                <p
                    x-show="error"
                    x-cloak
                    x-text="error"
                    class="mt-4 text-sm text-red-600 dark:text-red-400"
                ></p>

                <div
                    x-show="source"
                    x-cloak
                    class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-md bg-black/5 px-4 py-3 text-sm dark:bg-white/5"
                >
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-1">
                        <span class="font-mono" x-text="sourceName"></span>
                        <span class="opacity-70">
                            <span x-text="sourceWidth"></span>×<span x-text="sourceHeight"></span> ·
                            <span x-text="formatBytes(sourceBytes)"></span>
                        </span>
                    </div>
                    <flux:button size="sm" variant="subtle" x-on:click="clear">Replace</flux:button>
                </div>
            </div>

            <div
                x-show="source"
                x-cloak
                class="grid gap-6 lg:grid-cols-2"
            >
                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">2. Resize</flux:heading>

                    <div class="mb-6 grid grid-cols-[1fr_auto_1fr] items-end gap-2">
                        <flux:input type="number" min="1" max="4096" step="1" x-model.number="width" label="Width (px)" />
                        <button
                            type="button"
                            x-on:click="toggleLock"
                            :title="locked ? 'Unlink — width and height move independently' : 'Link — keep aspect ratio'"
                            :aria-pressed="locked"
                            class="mb-1 grid size-10 place-items-center rounded-md border transition"
                            :class="locked
                                ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                                : 'border-black/15 hover:border-black/40 dark:border-white/15 dark:hover:border-white/40'"
                        >
                            <flux:icon :name="'link'" class="size-4" x-show="locked" />
                            <flux:icon :name="'link-slash'" class="size-4" x-show="!locked" />
                        </button>
                        <flux:input type="number" min="1" max="4096" step="1" x-model.number="height" label="Height (px)" />
                    </div>

                    <flux:button class="mb-6" size="sm" variant="subtle" x-on:click="matchSource">
                        Match source size
                    </flux:button>

                    <div class="mb-6 grid gap-3">
                        <flux:label>Fit</flux:label>
                        <flux:radio.group x-model="fit">
                            <flux:radio value="contain" label="Contain" description="Fit inside, pad with background." />
                            <flux:radio value="cover" label="Cover" description="Fill the frame, crop overflow." />
                            <flux:radio value="stretch" label="Stretch" description="Squash to exact dimensions." />
                        </flux:radio.group>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <flux:select x-model="format" label="Format">
                            <template x-for="(meta, mime) in formats" :key="mime">
                                <option :value="mime" x-text="meta.label"></option>
                            </template>
                        </flux:select>
                        <div x-show="supportsQuality" x-cloak>
                            <div class="mb-2 flex items-baseline justify-between">
                                <flux:label>Quality</flux:label>
                                <flux:subheading class="font-mono tabular-nums"><span x-text="quality"></span>%</flux:subheading>
                            </div>
                            <flux:slider min="10" max="100" step="1" x-model.number="quality" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <flux:color-picker x-model="bg" label="Background (used for Contain & opaque formats)" copyable />
                    </div>
                </div>

                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Preview</flux:heading>

                    <div class="mb-6 grid place-items-center rounded-md border border-black/10 bg-[conic-gradient(at_50%_50%,_#0001_0deg_90deg,_transparent_90deg_180deg,_#0001_180deg_270deg,_transparent_270deg)] bg-[length:16px_16px] p-4 dark:border-white/10">
                        <img
                            x-show="previewUrl"
                            :src="previewUrl"
                            class="max-h-[360px] max-w-full"
                            alt="Preview"
                        />
                    </div>

                    <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                        <div class="rounded-md border border-black/10 px-3 py-2 dark:border-white/10">
                            <div class="opacity-60">Output</div>
                            <div class="font-mono tabular-nums">
                                <span x-text="width"></span>×<span x-text="height"></span>
                            </div>
                        </div>
                        <div class="rounded-md border border-black/10 px-3 py-2 dark:border-white/10">
                            <div class="opacity-60">Size</div>
                            <div class="font-mono tabular-nums" x-text="formatBytes(previewBytes)"></div>
                            <div
                                class="font-mono text-xs tabular-nums"
                                x-show="sizeDelta"
                                x-cloak
                                :class="sizeDelta?.tone"
                                x-text="sizeDelta?.label"
                            ></div>
                        </div>
                    </div>

                    <flux:button variant="primary" icon="arrow-down-tray" x-on:click="download" class="w-full">
                        Download
                    </flux:button>
                </div>
            </div>

            <div
                x-show="source"
                x-cloak
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
            >
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-black/10 pb-4 dark:border-white/10">
                    <flux:heading size="xl">4. Favicon pack</flux:heading>
                    <flux:button size="sm" icon="arrow-down-tray" x-on:click="downloadAllFavicons">Download all</flux:button>
                </div>
                <flux:subheading class="mb-6">
                    Each size uses the fit, format and background from above. Browsers may ask permission for the bulk download.
                </flux:subheading>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-9">
                    <template x-for="entry in favicons" :key="entry.size">
                        <button
                            type="button"
                            x-on:click="downloadFavicon(entry.size)"
                            x-bind:disabled="entry.busy"
                            class="group grid place-items-center gap-2 rounded-md border border-black/10 px-3 py-4 text-xs transition hover:border-zinc-900 hover:shadow-sm disabled:opacity-50 dark:border-white/10 dark:hover:border-white"
                        >
                            <span
                                class="grid place-items-center rounded bg-black/5 dark:bg-white/5"
                                :style="`width: ${Math.min(entry.size, 64)}px; height: ${Math.min(entry.size, 64)}px;`"
                            >
                                <flux:icon name="arrow-down-tray" class="size-4 opacity-50 group-hover:opacity-100" />
                            </span>
                            <span class="font-mono"><span x-text="entry.size"></span>×<span x-text="entry.size"></span></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Share</flux:heading>
                <flux:subheading class="mb-4">
                    The URL below carries your resize settings. Image data stays on your device — drop your own image after opening the link.
                </flux:subheading>
                <flux:input type="url" x-model="url" readonly copyable label="Share URL" />
            </div>
        </div>
    </div>
</x-layouts.app>
