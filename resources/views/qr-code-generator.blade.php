<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="qrCodeGenerator">

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon name="qr-code" class="size-[96px] text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">QR Code Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">Create QR codes for URLs, text, Wi-Fi, and more.</flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">1. Content</flux:heading>
                <flux:textarea
                    name="text"
                    x-model="text"
                    label="Text or URL"
                    placeholder="https://example.com"
                    rows="6"
                />
                <flux:subheading class="mt-3" size="sm">
                    Anything you'd put behind a link — URLs, plain text, contact details, Wi-Fi credentials.
                </flux:subheading>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">2. Preview</flux:heading>

                <div class="mb-6 grid place-items-center">
                    <div
                        class="rounded-md border border-black/10 p-4 dark:border-white/10"
                        :style="{ backgroundColor: bg }"
                    >
                        <canvas x-ref="canvas" class="block max-w-full h-auto"></canvas>
                    </div>
                </div>

                <p
                    x-show="!text"
                    class="mb-4 text-center text-sm opacity-60"
                >
                    Enter text or a URL to generate a QR code.
                </p>

                <div
                    x-show="contrastWarning && text"
                    x-cloak
                    class="mb-4 rounded-md border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-700 dark:text-amber-300"
                >
                    Low contrast between foreground and background — scanners may struggle.
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:button
                        type="button"
                        variant="primary"
                        x-on:click="downloadPng"
                        x-bind:disabled="!text"
                    >
                        Download PNG
                    </flux:button>
                    <flux:button
                        type="button"
                        x-on:click="downloadSvg"
                        x-bind:disabled="!text"
                    >
                        Download SVG
                    </flux:button>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 lg:col-span-2 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Customize</flux:heading>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <flux:input
                        type="number"
                        min="64"
                        max="2048"
                        step="32"
                        x-model.number="size"
                        label="Size (px)"
                    />
                    <flux:select x-model="ec" label="Error correction">
                        <flux:select.option value="L">L — ~7%</flux:select.option>
                        <flux:select.option value="M">M — ~15%</flux:select.option>
                        <flux:select.option value="Q">Q — ~25%</flux:select.option>
                        <flux:select.option value="H">H — ~30%</flux:select.option>
                    </flux:select>
                    <flux:input
                        type="number"
                        min="0"
                        max="16"
                        step="1"
                        x-model.number="margin"
                        label="Quiet zone (modules)"
                    />
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <flux:color-picker x-model="fg" label="Foreground" copyable />
                    <flux:color-picker x-model="bg" label="Background" copyable />
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 lg:col-span-2 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">4. Logo</flux:heading>
                <flux:subheading class="mb-6">
                    Drop a logo into the centre. We recommend error correction <strong>H</strong> when using a logo so scanners can still recover the code.
                </flux:subheading>

                <div class="mb-6">
                    <flux:label>Quick picks</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="(preset, key) in presets" :key="key">
                            <button
                                type="button"
                                x-on:click="applyPreset(key)"
                                :class="activePreset === key
                                    ? 'border-zinc-900 dark:border-white'
                                    : 'border-black/10 dark:border-white/10 hover:border-black/30 dark:hover:border-white/30'"
                                class="grid w-20 place-items-center gap-1 rounded-md border px-2 py-3 text-xs transition"
                                :title="preset.label"
                                :aria-pressed="activePreset === key"
                            >
                                <span class="size-6" x-html="preset.path
                                    ? `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor'>${preset.path}</svg>`
                                    : ''"></span>
                                <span x-text="preset.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[auto_1fr] lg:items-start">
                    <div class="flex items-start gap-4">
                        <div
                            class="grid size-24 place-items-center overflow-hidden rounded-md border border-black/10 bg-white dark:border-white/10 dark:bg-zinc-700"
                        >
                            <template x-if="logo">
                                <img :src="logo" class="size-full object-contain p-2" alt="Logo preview" />
                            </template>
                            <template x-if="!logo">
                                <flux:icon name="photo" class="size-8 text-zinc-400" />
                            </template>
                        </div>
                        <div class="flex flex-col gap-2">
                            <input
                                type="file"
                                accept="image/*"
                                x-ref="logoInput"
                                x-on:change="onLogoSelected"
                                class="hidden"
                            />
                            <flux:button
                                type="button"
                                size="sm"
                                x-on:click="$refs.logoInput.click()"
                            >
                                <span x-text="logo ? 'Replace with file' : 'Upload your own'"></span>
                            </flux:button>
                            <flux:button
                                type="button"
                                size="sm"
                                variant="subtle"
                                x-on:click="clearLogo"
                                x-show="logo"
                                x-cloak
                            >
                                Remove
                            </flux:button>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <flux:input
                            type="number"
                            min="5"
                            max="40"
                            step="1"
                            x-model.number="logoSize"
                            label="Logo size (% of QR width)"
                            description="Keep this under ~30% to stay scannable."
                        />
                        <flux:checkbox
                            x-model="logoPadding"
                            label="Add a clean background behind the logo"
                            description="Improves scanability when the logo overlaps dark modules."
                        />
                    </div>
                </div>

                <p
                    x-show="logoError"
                    x-cloak
                    x-text="logoError"
                    class="mt-4 text-sm text-red-600 dark:text-red-400"
                ></p>
            </div>

            <div class="rounded-lg border border-black/10 p-8 lg:col-span-2 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">5. Share</flux:heading>
                <flux:subheading class="mb-2">
                    The URL below carries every setting — anyone who opens it sees the same QR code.
                </flux:subheading>
                <p class="mb-4 text-sm opacity-60" x-show="logo" x-cloak>
                    Logos aren't included in the share URL. Download the PNG or SVG to share the QR with the logo embedded.
                </p>

                <flux:input
                    type="url"
                    x-model="url"
                    readonly
                    copyable
                    label="Share URL"
                />
            </div>
        </div>
    </div>
</x-layouts.app>
