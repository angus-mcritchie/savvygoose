<x-layouts.app>
    @php
        $units = [
            'length' => [
                'mm' => 'Millimeter (mm)', 'cm' => 'Centimeter (cm)', 'm' => 'Meter (m)',
                'km' => 'Kilometer (km)', 'in' => 'Inch (in)', 'ft' => 'Foot (ft)',
                'yd' => 'Yard (yd)', 'mi' => 'Mile (mi)', 'nmi' => 'Nautical mile (nmi)',
            ],
            'weight' => [
                'mg' => 'Milligram (mg)', 'g' => 'Gram (g)', 'kg' => 'Kilogram (kg)',
                't' => 'Tonne (t)', 'oz' => 'Ounce (oz)', 'lb' => 'Pound (lb)', 'st' => 'Stone (st)',
            ],
            'temperature' => [
                'c' => 'Celsius (°C)', 'f' => 'Fahrenheit (°F)', 'k' => 'Kelvin (K)',
            ],
            'data' => [
                'B' => 'Byte (B)', 'KB' => 'Kilobyte (KB)', 'MB' => 'Megabyte (MB)',
                'GB' => 'Gigabyte (GB)', 'TB' => 'Terabyte (TB)', 'PB' => 'Petabyte (PB)',
                'KiB' => 'Kibibyte (KiB)', 'MiB' => 'Mebibyte (MiB)', 'GiB' => 'Gibibyte (GiB)',
                'TiB' => 'Tebibyte (TiB)', 'PiB' => 'Pebibyte (PiB)',
            ],
        ];
    @endphp

    <div class="mx-auto max-w-[1000px]" x-data="unitConverter">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.scale class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Unit Converter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Convert length, weight, temperature, and data sizes.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
                <div class="mb-8 flex justify-center">
                    <flux:radio.group x-model="cat" variant="segmented" size="sm">
                        <flux:radio value="length" label="Length" />
                        <flux:radio value="weight" label="Weight" />
                        <flux:radio value="temperature" label="Temperature" />
                        <flux:radio value="data" label="Data" />
                    </flux:radio.group>
                </div>

                <div class="grid items-end gap-4 sm:grid-cols-[1fr_auto_1fr]">
                    <div class="grid gap-3">
                        <flux:input
                            type="number"
                            inputmode="decimal"
                            step="any"
                            x-model="value"
                            label="From"
                        />
                        <flux:select variant="listbox" x-model="from">
                            @foreach ($units as $catKey => $unitMap)
                                @foreach ($unitMap as $unitKey => $unitLabel)
                                    <flux:select.option
                                        value="{{ $unitKey }}"
                                        x-show="cat === '{{ $catKey }}'"
                                    >{{ $unitLabel }}</flux:select.option>
                                @endforeach
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="flex items-center justify-center pb-1">
                        <flux:button
                            type="button"
                            x-on:click="swap()"
                            icon="arrows-right-left"
                            size="sm"
                            variant="subtle"
                            aria-label="Swap units"
                        />
                    </div>

                    <div class="grid gap-3">
                        <flux:input
                            type="number"
                            inputmode="decimal"
                            step="any"
                            x-model="result"
                            label="To"
                        />
                        <flux:select variant="listbox" x-model="to">
                            @foreach ($units as $catKey => $unitMap)
                                @foreach ($unitMap as $unitKey => $unitLabel)
                                    <flux:select.option
                                        value="{{ $unitKey }}"
                                        x-show="cat === '{{ $catKey }}'"
                                    >{{ $unitLabel }}</flux:select.option>
                                @endforeach
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <p
                    class="mt-8 text-center font-mono text-sm text-zinc-500 dark:text-zinc-400"
                    x-show="formula()"
                    x-cloak
                    x-text="formula()"
                ></p>
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
