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
                        Resize & convert images, entirely in your browser.
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
                        or use the button below. PNG, JPEG, WebP, SVG, GIF, up to 20 MB.
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

                    <flux:label class="mb-2">Canvas size</flux:label>
                    <flux:subheading class="mb-3 text-xs">The output frame. Free to be any dimensions.</flux:subheading>
                    <div class="mb-4 grid grid-cols-2 gap-2">
                        <flux:input type="number" min="1" max="4096" step="1" x-model.number="canvasWidth" label="Width (px)" />
                        <flux:input type="number" min="1" max="4096" step="1" x-model.number="canvasHeight" label="Height (px)" />
                    </div>

                    <div class="mb-6 grid gap-3">
                        <flux:label>Quick sizes</flux:label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="preset in sizePresets" :key="preset.label">
                                <button
                                    type="button"
                                    x-on:click="applyCanvasSize(preset.w, preset.h)"
                                    :class="canvasWidth === preset.w && canvasHeight === preset.h
                                        ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                                        : 'border-black/15 hover:border-black/40 dark:border-white/15 dark:hover:border-white/40'"
                                    class="rounded-md border px-3 py-1.5 font-mono text-xs transition"
                                    x-text="preset.label"
                                ></button>
                            </template>
                            <flux:button size="sm" variant="subtle" x-on:click="canvasMatchSource">Match source</flux:button>
                        </div>
                    </div>

                    <div class="mb-6 grid gap-3">
                        <flux:label>Aspect ratio</flux:label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="ratio in ratioPresets" :key="ratio.label">
                                <button
                                    type="button"
                                    x-on:click="applyCanvasRatio(ratio.w, ratio.h)"
                                    class="rounded-md border border-black/15 px-3 py-1.5 font-mono text-xs transition hover:border-black/40 dark:border-white/15 dark:hover:border-white/40"
                                    x-text="ratio.label"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <flux:separator class="my-6" />

                    <flux:label class="mb-2">Image size & transform</flux:label>
                    <flux:subheading class="mb-3 text-xs">
                        Drag the image in the preview to move it; use the corner handles to scale, the lollipop to rotate.
                        Hold <kbd class="rounded border border-black/15 px-1 font-mono text-[0.65rem] dark:border-white/15">Shift</kbd> to snap — 15° when rotating, 25% of source when scaling.
                        Source: <span class="font-mono tabular-nums" x-text="sourceWidth"></span>×<span class="font-mono tabular-nums" x-text="sourceHeight"></span>.
                    </flux:subheading>

                    <div class="mb-3 flex flex-wrap items-center gap-2 text-xs" x-show="source" x-cloak>
                        <span
                            class="font-mono tabular-nums"
                            :class="isUpscaled ? 'text-amber-600 dark:text-amber-400' : 'opacity-60'"
                        >Scale: <span x-text="scaleLabel"></span></span>
                        <span
                            x-show="isUpscaled"
                            x-cloak
                            class="rounded border border-amber-500/40 bg-amber-500/10 px-1.5 py-0.5 text-[0.65rem] font-medium uppercase tracking-wide text-amber-700 dark:text-amber-400"
                            title="Image is rendered larger than the source's natural pixels — may look soft."
                        >Upscaled</span>
                        <span
                            x-show="isStretched"
                            x-cloak
                            class="rounded border border-amber-500/40 bg-amber-500/10 px-1.5 py-0.5 text-[0.65rem] font-medium uppercase tracking-wide text-amber-700 dark:text-amber-400"
                            title="Width and height aspect differs from the source — image is distorted."
                        >Stretched</span>
                    </div>
                    <div class="mb-4 grid grid-cols-[1fr_auto_1fr] items-end gap-2">
                        <flux:input type="number" min="1" max="4096" step="1" x-model.number="imageWidth" label="Width (px)" />
                        <button
                            type="button"
                            x-on:click="toggleLock"
                            :title="locked ? 'Unlink: width and height move independently' : 'Link: keep aspect ratio'"
                            :aria-pressed="locked"
                            class="grid size-10 place-items-center rounded-md border transition"
                            :class="locked
                                ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                                : 'border-black/15 hover:border-black/40 dark:border-white/15 dark:hover:border-white/40'"
                        >
                            <flux:icon :name="'link'" class="size-4" x-show="locked" />
                            <flux:icon :name="'link-slash'" class="size-4" x-show="!locked" />
                        </button>
                        <flux:input type="number" min="1" max="4096" step="1" x-model.number="imageHeight" label="Height (px)" />
                    </div>

                    <div class="mb-4 grid grid-cols-3 gap-2">
                        <flux:input type="number" step="1" x-model.number="imageX" label="X (px)" />
                        <flux:input type="number" step="1" x-model.number="imageY" label="Y (px)" />
                        <flux:input type="number" min="-180" max="180" step="1" x-model.number="imageRotation" label="Rotation (°)" />
                    </div>

                    <div class="mb-4">
                        <flux:checkbox
                            x-model="allowExceedCanvas"
                            label="Allow image larger than canvas"
                            description="When off, typed sizes and presets are clamped so the image fits within the canvas."
                        />
                    </div>

                    <div class="mb-6 flex flex-wrap gap-2">
                        <flux:button size="sm" variant="subtle" x-on:click="matchSource">Match source</flux:button>
                        <flux:button size="sm" variant="subtle" x-on:click="fitImageToCanvas">Fit to canvas</flux:button>
                        <flux:button size="sm" variant="subtle" x-on:click="centerImage">Center</flux:button>
                    </div>

                    <flux:separator class="my-6" />

                    <div class="grid gap-6 sm:grid-cols-2">
                        <flux:field>
                            <div class="flex items-center gap-1">
                                <flux:label>Format</flux:label>
                                <flux:dropdown position="bottom" align="start">
                                    <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="Which format should I pick?" />
                                    <flux:popover class="max-w-sm">
                                        <flux:heading size="sm">Picking a format</flux:heading>
                                        <ul class="mt-2 space-y-2 text-sm">
                                            <li><strong>PNG</strong>: lossless, supports transparency. Best for logos, icons, screenshots.</li>
                                            <li><strong>JPEG</strong>: lossy, no transparency. Best for photos.</li>
                                            <li><strong>WebP</strong>: modern; smaller than both at similar quality. Universally supported in modern browsers.</li>
                                        </ul>
                                    </flux:popover>
                                </flux:dropdown>
                            </div>
                            <flux:select x-model="format">
                                <template x-for="(meta, mime) in formats" :key="mime">
                                    <option :value="mime" x-text="meta.label"></option>
                                </template>
                            </flux:select>
                        </flux:field>
                        <div x-show="supportsQuality" x-cloak>
                            <div class="mb-2 flex items-baseline justify-between">
                                <flux:label>Quality</flux:label>
                                <flux:subheading class="font-mono tabular-nums"><span x-text="quality"></span>%</flux:subheading>
                            </div>
                            <flux:slider min="10" max="100" step="1" x-model.number="quality" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <flux:label class="mb-2">Background</flux:label>
                        <flux:checkbox
                            x-model="transparent"
                            x-bind:disabled="!supportsTransparency"
                            label="Transparent"
                            description="Leave the canvas around the image transparent. JPEG always fills."
                        />
                        <div class="mt-3" x-show="!transparent || !supportsTransparency" x-cloak>
                            <flux:color-picker x-model="bg" copyable />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Preview</flux:heading>

                    <div
                        class="mb-6 flex justify-center overflow-hidden rounded-md border border-black/10 bg-zinc-50 p-4 select-none dark:border-white/10 dark:bg-zinc-900/40"
                        x-on:pointermove.window="onPointerMove($event)"
                        x-on:pointerup.window="onPointerUp($event)"
                        x-on:pointercancel.window="onPointerUp($event)"
                    >
                        <div class="relative max-w-full">
                            <canvas
                                x-ref="preview"
                                class="block max-h-[360px] max-w-full bg-[conic-gradient(at_50%_50%,_#0001_0deg_90deg,_transparent_90deg_180deg,_#0001_180deg_270deg,_transparent_270deg)] bg-[length:16px_16px] outline outline-1 outline-black/15 dark:outline-white/20"
                            ></canvas>
                            <div
                                class="pointer-events-none absolute inset-0"
                                x-show="source"
                                x-cloak
                            >
                                <div
                                    class="pointer-events-none absolute origin-center"
                                    :style="`left:${imageScreenLeft}px;top:${imageScreenTop}px;width:${imageScreenWidth}px;height:${imageScreenHeight}px;transform:translate(-50%,-50%) rotate(${imageRotation}deg);`"
                                >
                                    <div
                                        class="pointer-events-auto absolute inset-0 cursor-move border border-dashed border-sky-500/80 touch-none"
                                        x-on:pointerdown="startDrag($event)"
                                    ></div>
                                    <div
                                        class="pointer-events-auto absolute -left-1.5 -top-1.5 size-3 cursor-nwse-resize rounded-sm border border-sky-500 bg-white shadow touch-none dark:bg-zinc-900"
                                        x-on:pointerdown="startScale($event, 'nw')"
                                        x-on:dblclick="resetImageSize()"
                                        title="Drag to scale · double-click to reset to source"
                                    ></div>
                                    <div
                                        class="pointer-events-auto absolute -right-1.5 -top-1.5 size-3 cursor-nesw-resize rounded-sm border border-sky-500 bg-white shadow touch-none dark:bg-zinc-900"
                                        x-on:pointerdown="startScale($event, 'ne')"
                                        x-on:dblclick="resetImageSize()"
                                        title="Drag to scale · double-click to reset to source"
                                    ></div>
                                    <div
                                        class="pointer-events-auto absolute -bottom-1.5 -left-1.5 size-3 cursor-nesw-resize rounded-sm border border-sky-500 bg-white shadow touch-none dark:bg-zinc-900"
                                        x-on:pointerdown="startScale($event, 'sw')"
                                        x-on:dblclick="resetImageSize()"
                                        title="Drag to scale · double-click to reset to source"
                                    ></div>
                                    <div
                                        class="pointer-events-auto absolute -bottom-1.5 -right-1.5 size-3 cursor-nwse-resize rounded-sm border border-sky-500 bg-white shadow touch-none dark:bg-zinc-900"
                                        x-on:pointerdown="startScale($event, 'se')"
                                        x-on:dblclick="resetImageSize()"
                                        title="Drag to scale · double-click to reset to source"
                                    ></div>
                                    <div class="pointer-events-none absolute -top-6 left-1/2 h-6 w-px -translate-x-1/2 bg-sky-500"></div>
                                    <div
                                        class="pointer-events-auto absolute -top-8 left-1/2 size-3 -translate-x-1/2 cursor-grab rounded-full border border-sky-500 bg-white shadow touch-none dark:bg-zinc-900"
                                        x-on:pointerdown="startRotate($event)"
                                        x-on:dblclick="resetRotation()"
                                        title="Drag to rotate · double-click to reset to 0°"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                        <div class="rounded-md border border-black/10 px-3 py-2 dark:border-white/10">
                            <div class="opacity-60">Canvas</div>
                            <div class="font-mono tabular-nums">
                                <span x-text="canvasWidth"></span>×<span x-text="canvasHeight"></span>
                            </div>
                            <div class="font-mono text-xs tabular-nums opacity-60">
                                Image <span x-text="imageWidth"></span>×<span x-text="imageHeight"></span>
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

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Share</flux:heading>
                <flux:subheading class="mb-4">
                    The URL below carries your resize settings. Image data stays on your device, so drop your own image after opening the link.
                </flux:subheading>
                <flux:input type="url" x-model="url" readonly copyable label="Share URL" />
            </div>
        </div>
    </div>
</x-layouts.app>
