<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="qrCodeGenerator">

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon name="qr-code" class="size-20 text-zinc-700 dark:text-zinc-200" />
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
                    Anything you'd put behind a link: URLs, plain text, contact details, Wi-Fi credentials.
                </flux:subheading>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">2. Preview</flux:heading>

                <div class="mb-6 grid place-items-center">
                    <div
                        class="rounded-md border border-black/10 p-4 dark:border-white/10"
                        :style="{ backgroundColor: bg }"
                    >
                        {{-- Sized up front to the default `size` (DEFAULTS.size in
                             resources/js/data/qrCodeGenerator.js); without it the canvas sits at its
                             intrinsic 300×150 until the first render and the card resizes. --}}
                        <canvas x-ref="canvas" width="256" height="256" class="block max-w-full h-auto"></canvas>
                    </div>
                </div>

                <p
                    x-show="!text"
                    class="mb-4 text-center text-sm opacity-60"
                >
                    Enter text or a URL to generate a QR code.
                </p>

                <div
                    x-show="contrastWarning && text && !capacityError"
                    x-cloak
                    class="mb-4 rounded-md border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-700 dark:text-amber-300"
                >
                    Low contrast between foreground and background. Scanners may struggle.
                </div>

                <div
                    x-show="capacityError || renderError || downloadError"
                    x-cloak
                    class="mb-4 rounded-md border border-red-500/40 bg-red-500/10 px-3 py-2 text-sm text-red-700 dark:text-red-300"
                    x-text="capacityError || renderError || downloadError"
                ></div>

                <p
                    x-show="logoClamped"
                    x-cloak
                    class="mb-4 text-center text-sm opacity-60"
                >
                    The logo was trimmed to fit what this code can spare. Raise the error correction, or encode less
                    text, for room to grow it.
                </p>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:button
                        type="button"
                        variant="primary"
                        x-on:click="downloadPng"
                        x-bind:disabled="!text || !!capacityError || !!renderError"
                    >
                        Download PNG
                    </flux:button>
                    <flux:button
                        type="button"
                        x-on:click="downloadSvg"
                        x-bind:disabled="!text || !!capacityError"
                    >
                        Download SVG
                    </flux:button>
                </div>

                <p
                    x-show="exportSize && exportSize > size"
                    x-cloak
                    class="mt-3 text-center text-sm opacity-60"
                >
                    This code needs more room than <span x-text="size"></span> px, so the PNG exports at
                    <span x-text="exportSize"></span> px to stay readable. The SVG is resolution independent.
                </p>
            </div>

            <div class="rounded-lg border border-black/10 p-8 lg:col-span-2 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Design</flux:heading>

                <div class="mb-8">
                    <flux:label>Theme</flux:label>
                    <flux:subheading class="mt-1" size="sm">
                        A starting point. Each one just sets the two shape controls below, so you can mix your own.
                    </flux:subheading>
                    {{-- Buttons are rendered server-side so the picker holds its space; only the
                         swatch art is client-side (it is a real QR drawn in each preset). Keys and
                         labels mirror QR_PRESETS in resources/js/lib/qrRenderer.js. --}}
                    <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-6">
                        @foreach (['classic' => 'Classic', 'rounded' => 'Rounded', 'fluid' => 'Fluid', 'dots' => 'Dots', 'circles' => 'Circles', 'leaf' => 'Leaf'] as $key => $label)
                            <button
                                type="button"
                                x-on:click="applyTheme('{{ $key }}')"
                                :class="activeTheme === '{{ $key }}'
                                    ? 'border-zinc-900 dark:border-white'
                                    : 'border-black/10 dark:border-white/10 hover:border-black/30 dark:hover:border-white/30'"
                                class="grid place-items-center gap-2 rounded-md border border-black/10 p-3 text-xs transition dark:border-white/10"
                                :aria-pressed="activeTheme === '{{ $key }}'"
                            >
                                {{-- aspect-square keeps the slot the same height empty or filled. --}}
                                <span
                                    class="aspect-square w-full max-w-16 text-zinc-800 dark:text-zinc-100"
                                    x-html="themeThumbs['{{ $key }}']"
                                ></span>
                                <span>{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mb-8 grid gap-6 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Module shape</flux:label>
                        <flux:select x-model="mod">
                            <flux:select.option value="square">Square</flux:select.option>
                            <flux:select.option value="rounded">Rounded</flux:select.option>
                            <flux:select.option value="fluid">Fluid</flux:select.option>
                            <flux:select.option value="dot">Dots</flux:select.option>
                        </flux:select>
                        <flux:description>How the data squares are drawn.</flux:description>
                    </flux:field>
                    <flux:field>
                        <flux:label>Corner shape</flux:label>
                        <flux:select x-model="eye">
                            <flux:select.option value="square">Square</flux:select.option>
                            <flux:select.option value="rounded">Rounded</flux:select.option>
                            <flux:select.option value="circle">Circle</flux:select.option>
                            <flux:select.option value="leaf">Leaf</flux:select.option>
                        </flux:select>
                        <flux:description>Applies to the three finder squares scanners look for.</flux:description>
                    </flux:field>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <flux:input
                        type="number"
                        min="64"
                        max="2048"
                        step="32"
                        x-model.number="size"
                        label="Size (px)"
                    />
                    <flux:field>
                        <div class="flex items-center gap-1">
                            <flux:label>Error correction</flux:label>
                            <flux:dropdown position="bottom" align="start">
                                <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="What is error correction?" />
                                <flux:popover class="max-w-sm">
                                    <flux:heading size="sm">Error correction</flux:heading>
                                    <p class="mt-2 text-sm">QR codes embed redundant data so they still scan when partly damaged or obscured.</p>
                                    <flux:separator class="my-3" />
                                    <ul class="space-y-1 text-sm">
                                        <li><strong>L</strong>: ~7% recoverable. Smallest code; clean prints.</li>
                                        <li><strong>M</strong>: ~15%. Sensible default.</li>
                                        <li><strong>Q</strong>: ~25%. Outdoors or with light scuffing.</li>
                                        <li><strong>H</strong>: ~30%. Use whenever a logo overlaps the code.</li>
                                    </ul>
                                    <flux:separator class="my-3" />
                                    <p class="text-sm">Higher levels make the code denser, so it needs a bigger size or more pixels per module.</p>
                                </flux:popover>
                            </flux:dropdown>
                        </div>
                        <flux:select x-model="ec">
                            <flux:select.option value="L">L (~7%)</flux:select.option>
                            <flux:select.option value="M">M (~15%)</flux:select.option>
                            <flux:select.option value="Q">Q (~25%)</flux:select.option>
                            <flux:select.option value="H">H (~30%)</flux:select.option>
                        </flux:select>
                    </flux:field>
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
                    Drop a logo into the centre. The modules underneath come out whole, so none are left half-drawn, and how big it can go depends on your error correction — that redundancy is what rebuilds the covered data. Level <strong>H</strong> gives you the most room.
                </flux:subheading>

                <div
                    x-show="ecRaised"
                    x-cloak
                    class="mb-6 rounded-md border border-black/10 px-3 py-2 text-sm opacity-70 dark:border-white/10"
                >
                    Error correction was raised to <strong>H</strong> to make room for the logo. Lower it again under
                    Design if you'd rather have the smaller, simpler code.
                </div>

                <div class="mb-6">
                    <flux:label>Quick picks</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        {{-- Labels mirror PRESET_ICONS in resources/js/data/qrCodeGenerator.js, which
                             also holds the icon path drawn into the fixed size-6 slot. --}}
                        @foreach (['url' => 'URL', 'wifi' => 'Wi-Fi', 'contact' => 'Contact', 'email' => 'Email', 'phone' => 'Phone', 'location' => 'Location'] as $key => $label)
                            <button
                                type="button"
                                x-on:click="applyPreset('{{ $key }}')"
                                :class="activePreset === '{{ $key }}'
                                    ? 'border-zinc-900 dark:border-white'
                                    : 'border-black/10 dark:border-white/10 hover:border-black/30 dark:hover:border-white/30'"
                                class="grid w-20 place-items-center gap-1 rounded-md border border-black/10 px-2 py-3 text-xs transition dark:border-white/10"
                                title="{{ $label }}"
                                :aria-pressed="activePreset === '{{ $key }}'"
                            >
                                <span class="size-6" x-html="presets['{{ $key }}'].path
                                    ? `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor'>${presets['{{ $key }}'].path}</svg>`
                                    : ''"></span>
                                <span>{{ $label }}</span>
                            </button>
                        @endforeach
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
                            step="1"
                            x-bind:max="maxLogoSize"
                            x-model.number="logoSize"
                            label="Logo size (% of code width)"
                        />
                        <flux:description class="-mt-2">
                            Capped at <span x-text="maxLogoSize"></span>% by the current error correction, which is what
                            rebuilds the modules the logo covers. Level H allows the most.
                        </flux:description>
                        <flux:checkbox
                            x-model="logoClearance"
                            label="Clear the modules behind the logo"
                            description="Drops whole modules to make room, so none are left half-drawn. Turn it off to lay the logo straight over the code."
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
                <x-share-field
                    :heading="false"
                    subheading="The URL below carries every setting. Anyone who opens it sees the same QR code."
                >
                    <p class="mt-3 text-sm opacity-60" x-show="logo" x-cloak>
                        Logos aren't included in the share URL. Download the PNG or SVG to share the QR with the logo embedded.
                    </p>
                </x-share-field>
            </div>
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
