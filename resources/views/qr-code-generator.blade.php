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

        {{-- Two columns on desktop: settings run down the left, the preview holds the right
             and sticks while you scroll past it. The preview spans every row so its grid area
             is the full height of the column, which is what gives `sticky` room to work; the
             row count has to match the number of cards on the left. Placement is explicit so
             the source order stays Content, Preview, then the rest, which is the reading order
             on a phone. --}}
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-start">
            <div class="rounded-lg border border-black/10 p-8 lg:col-start-1 dark:border-white/10">
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

            <div class="rounded-lg border border-black/10 p-8 lg:sticky lg:top-6 lg:col-start-2 lg:row-span-5 lg:row-start-1 dark:border-white/10">
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

            <div class="rounded-lg border border-black/10 p-8 lg:col-start-1 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Design</flux:heading>

                <div class="mb-8">
                    <flux:label>Theme</flux:label>
                    <flux:subheading class="mt-1" size="sm">
                        A starting point. Each one just sets the module and corner shapes, which you can mix
                        yourself under Advanced options.
                    </flux:subheading>
                    {{-- Buttons are rendered server-side so the picker holds its space; only the
                         swatch art is client-side (it is a real QR drawn in each preset). Keys and
                         labels mirror QR_PRESETS in resources/js/lib/qrRenderer.js. --}}
                    <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-6">
                        @foreach (['classic' => 'Classic', 'rounded' => 'Rounded', 'fluid' => 'Fluid', 'dots' => 'Dots', 'circles' => 'Circles', 'leaf' => 'Leaf'] as $key => $label)
                            <button
                                type="button"
                                x-on:click="applyTheme('{{ $key }}')"
                                {{-- Selection is a ring rather than a border colour: a bound
                                     border-* class loses to the static one whichever way the
                                     stylesheet happens to be ordered, and a ring adds no width. --}}
                                :class="activeTheme === '{{ $key }}' ? 'ring-2 ring-zinc-900 dark:ring-white' : ''"
                                class="grid place-items-center gap-2 rounded-md border border-black/10 p-3 text-xs transition hover:border-black/30 dark:border-white/10 dark:hover:border-white/30"
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

                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:color-picker x-model="fg" label="Foreground" copyable />
                    <flux:color-picker x-model="bg" label="Background" copyable />
                </div>

                {{-- Everything below is either covered by a theme or fine at its default, so
                     it starts folded away. A shared link that changed any of it opens the
                     panel server-side, which keeps the settings visible without a layout
                     shift once Alpine boots. --}}
                <flux:accordion
                    class="mt-8 border-t border-black/10 pt-4 dark:border-white/10"
                    transition
                >
                    <flux:accordion.item :expanded="request()->hasAny(['mod', 'eye', 'size', 'ec', 'margin'])">
                        <flux:accordion.heading>Advanced options</flux:accordion.heading>
                        <flux:accordion.content>
                            <flux:subheading class="mb-6" size="sm">
                                Mix your own shapes, or change how the code is encoded and exported.
                            </flux:subheading>

                            <div class="grid gap-6 sm:grid-cols-2">
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

                            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
            </div>

            <div class="rounded-lg border border-black/10 p-8 lg:col-start-1 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">4. Frame</flux:heading>
                <flux:subheading class="mb-6">
                    Optional. A border, or a caption telling people what the code is for. Frames sit outside the code,
                    so they never affect whether it scans, and they are baked into both downloads.
                </flux:subheading>

                {{-- Like the theme picker: buttons render server-side, only the swatch art is
                     client-side. Frames need a real backdrop for the caption bar to read
                     against, hence the --qr-swatch-bg variable the SVG paints with. --}}
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-6">
                    @foreach (['none' => 'None', 'border' => 'Border', 'corners' => 'Corners', 'label' => 'Caption', 'label-top' => 'Caption top', 'bubble' => 'Bubble'] as $key => $label)
                        <button
                            type="button"
                            x-on:click="setFrame('{{ $key }}')"
                            :class="frame === '{{ $key }}' ? 'ring-2 ring-zinc-900 dark:ring-white' : ''"
                            class="grid place-items-center gap-2 rounded-md border border-black/10 p-3 text-xs transition hover:border-black/30 dark:border-white/10 dark:hover:border-white/30"
                            :aria-pressed="frame === '{{ $key }}'"
                        >
                            <span
                                class="grid aspect-square w-full max-w-16 place-items-center text-zinc-800 [--qr-swatch-bg:#ffffff] [&>svg]:max-h-full dark:text-zinc-100 dark:[--qr-swatch-bg:#3f3f46]"
                                x-html="frameThumbs['{{ $key }}']"
                            ></span>
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 max-w-sm" x-show="captionEnabled" x-cloak>
                    <flux:input
                        x-model="caption"
                        label="Caption"
                        maxlength="40"
                        placeholder="SCAN ME"
                    />
                    <flux:description class="mt-2">
                        Keep it short. Longer wording shrinks to fit the bar rather than spilling out of it.
                    </flux:description>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 lg:col-start-1 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">5. Logo</flux:heading>
                <flux:subheading class="mb-6">
                    Optional. Pick a mark for the centre, or upload your own. Error correction goes to
                    <strong>H</strong> to cover it, since that redundancy is what rebuilds the modules underneath.
                </flux:subheading>

                <input
                    type="file"
                    accept="image/*"
                    x-ref="logoInput"
                    x-on:change="onLogoSelected"
                    class="hidden"
                />

                {{-- Presets and upload share one row of tiles: the choice is "which logo", and
                     splitting it across a preset strip and a separate upload panel made it read
                     as two. Labels mirror PRESET_ICONS in resources/js/data/qrCodeGenerator.js. --}}
                <div class="grid grid-cols-4 gap-3 sm:grid-cols-7">
                    @foreach (['url' => 'URL', 'wifi' => 'Wi-Fi', 'contact' => 'Contact', 'email' => 'Email', 'phone' => 'Phone', 'location' => 'Location'] as $key => $label)
                        <button
                            type="button"
                            x-on:click="applyPreset('{{ $key }}')"
                            :class="activePreset === '{{ $key }}' ? 'ring-2 ring-zinc-900 dark:ring-white' : ''"
                            class="grid place-items-center gap-1 rounded-md border border-black/10 px-2 py-3 text-xs transition hover:border-black/30 dark:border-white/10 dark:hover:border-white/30"
                            :aria-pressed="activePreset === '{{ $key }}'"
                        >
                            <span class="size-6" x-html="`<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor'>${presets['{{ $key }}'].path}</svg>`"></span>
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach

                    {{-- Doubles as the thumbnail once a file is chosen, so an uploaded logo shows
                         up in the same place the preset marks do. --}}
                    <button
                        type="button"
                        x-on:click="$refs.logoInput.click()"
                        :class="logo && !activePreset ? 'ring-2 ring-zinc-900 dark:ring-white' : ''"
                        class="grid place-items-center gap-1 rounded-md border border-black/10 px-2 py-3 text-xs transition hover:border-black/30 dark:border-white/10 dark:hover:border-white/30"
                        :aria-pressed="!!logo && !activePreset"
                    >
                        <template x-if="logo && !activePreset">
                            <img :src="logo" class="size-6 object-contain" alt="" />
                        </template>
                        <template x-if="!logo || activePreset">
                            <flux:icon name="arrow-up-tray" class="size-6" />
                        </template>
                        <span>Upload</span>
                    </button>
                </div>

                {{-- Nothing below applies without a logo, so it stays out of the way until there
                     is one. --}}
                <div x-show="logo" x-cloak class="mt-6 grid gap-5">
                    <flux:field>
                        <flux:label>Size</flux:label>
                        <div class="flex items-center gap-3">
                            <input
                                type="range"
                                min="5"
                                step="1"
                                x-bind:max="maxLogoSize"
                                x-model.number="logoSize"
                                class="h-1.5 w-full accent-zinc-900 dark:accent-white"
                                aria-label="Logo size, percent of code width"
                            />
                            <span class="w-10 shrink-0 text-sm tabular-nums opacity-70">
                                <span x-text="logoSize"></span>%
                            </span>
                        </div>
                        <flux:description>
                            Up to <span x-text="maxLogoSize"></span>% at error correction
                            <span x-text="ec"></span>, which is what rebuilds the modules underneath.
                        </flux:description>
                    </flux:field>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <flux:checkbox
                            x-model="logoClearance"
                            label="Clear the modules behind the logo"
                            description="Drops whole modules to make room, so none are left half-drawn."
                        />
                        <flux:button type="button" size="sm" variant="subtle" x-on:click="clearLogo">
                            Remove logo
                        </flux:button>
                    </div>
                </div>

                <p
                    x-show="logoError"
                    x-cloak
                    x-text="logoError"
                    class="mt-4 text-sm text-red-600 dark:text-red-400"
                ></p>
            </div>

            <div class="rounded-lg border border-black/10 p-8 lg:col-start-1 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">6. Share</flux:heading>
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
