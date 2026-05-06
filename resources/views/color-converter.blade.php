<x-layouts.app>
    <div class="mx-auto max-w-[1100px]" x-data="colorConverter">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.swatch class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Color Converter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        HEX ↔ RGB ↔ HSL with a live swatch and contrast checker.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach (['fg' => 'Foreground', 'bg' => 'Background'] as $key => $label)
                    <div class="rounded-lg border border-black/10 p-6 dark:border-white/10">
                        <flux:heading class="mb-4" size="lg">{{ $label }}</flux:heading>

                        <div class="grid gap-4">
                            <flux:color-picker
                                x-model="{{ $key }}"
                                label="HEX"
                                copyable
                                dropper
                            />

                            <div class="grid gap-2">
                                <flux:label>RGB</flux:label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach (['r' => 'Red (0–255)', 'g' => 'Green (0–255)', 'b' => 'Blue (0–255)'] as $ch => $chLabel)
                                        <flux:input
                                            type="number"
                                            min="0"
                                            max="255"
                                            step="1"
                                            x-bind:value="rgb('{{ $key }}').{{ $ch }}"
                                            x-on:input="setRgb('{{ $key }}', '{{ $ch }}', $event.target.value)"
                                            aria-label="{{ $label }} {{ $chLabel }}"
                                            placeholder="{{ strtoupper($ch) }}"
                                        />
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <flux:label>HSL</flux:label>
                                <div class="grid grid-cols-3 gap-2">
                                    <flux:input
                                        type="number"
                                        step="1"
                                        x-bind:value="hsl('{{ $key }}').h"
                                        x-on:input="setHsl('{{ $key }}', 'h', $event.target.value)"
                                        aria-label="{{ $label }} hue (0–360°)"
                                        placeholder="H°"
                                    />
                                    <flux:input
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="1"
                                        x-bind:value="hsl('{{ $key }}').s"
                                        x-on:input="setHsl('{{ $key }}', 's', $event.target.value)"
                                        aria-label="{{ $label }} saturation (0–100%)"
                                        placeholder="S%"
                                    />
                                    <flux:input
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="1"
                                        x-bind:value="hsl('{{ $key }}').l"
                                        x-on:input="setHsl('{{ $key }}', 'l', $event.target.value)"
                                        aria-label="{{ $label }} lightness (0–100%)"
                                        placeholder="L%"
                                    />
                                </div>
                            </div>

                            <div class="grid gap-1 pt-2 text-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-zinc-500 dark:text-zinc-400">CSS rgb()</span>
                                    <code class="font-mono text-zinc-800 dark:text-zinc-100" x-text="rgbString('{{ $key }}')"></code>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-zinc-500 dark:text-zinc-400">CSS hsl()</span>
                                    <code class="font-mono text-zinc-800 dark:text-zinc-100" x-text="hslString('{{ $key }}')"></code>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-black/10 pb-4 dark:border-white/10">
                    <flux:heading size="xl">Contrast</flux:heading>
                    <flux:button type="button" size="sm" icon="arrows-right-left" x-on:click="swap()">
                        Swap
                    </flux:button>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div
                        class="flex h-44 flex-col items-center justify-center gap-2 rounded-lg border border-black/10 p-6 dark:border-white/10"
                        :style="{ backgroundColor: bg, color: fg }"
                    >
                        <span class="text-sm">Sample small text — 14px regular</span>
                        <span class="text-xl font-semibold">Sample large text — 20px bold</span>
                    </div>

                    <div class="grid gap-3">
                        <div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">Contrast ratio</div>
                            <div class="font-mono text-3xl font-semibold tabular-nums" x-text="contrastFormatted()"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm">
                            @foreach ([['AA', 'normal', 'AA · Normal'], ['AA', 'large', 'AA · Large'], ['AAA', 'normal', 'AAA · Normal'], ['AAA', 'large', 'AAA · Large']] as [$level, $size, $caption])
                                <div class="flex items-center gap-2">
                                    <flux:badge
                                        size="sm"
                                        x-bind:color="rating('{{ $level }}', '{{ $size }}') ? 'green' : 'red'"
                                        x-bind:icon="rating('{{ $level }}', '{{ $size }}') ? 'check' : 'x-mark'"
                                        x-text="rating('{{ $level }}', '{{ $size }}') ? 'Pass' : 'Fail'"
                                    >Pass</flux:badge>
                                    <span>{{ $caption }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
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
