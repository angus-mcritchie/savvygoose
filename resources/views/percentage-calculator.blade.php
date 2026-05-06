<x-layouts.app>
    @php
        $inputClass = 'w-36 sm:w-48 rounded-lg border border-zinc-200 dark:border-white/10 bg-white dark:bg-white/10 px-3 py-3 text-center text-2xl sm:text-3xl font-semibold text-zinc-800 dark:text-zinc-100 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none';
        $outputClass = 'min-w-36 sm:min-w-48 rounded-lg border border-blue-200 dark:border-blue-400/30 bg-blue-50 dark:bg-blue-500/10 px-3 py-3 text-center text-2xl sm:text-3xl font-semibold text-blue-700 dark:text-blue-200 tabular-nums';
        $opClass = 'text-xl sm:text-2xl font-medium text-zinc-500 dark:text-zinc-400';
    @endphp

    <div class="mx-auto grid max-w-4xl gap-12">
        <div class="flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <img class="mx-auto w-[128px]" src="{{ asset('image/discount.png') }}" width="128" height="128">
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Percentage Calculator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Calculate percentages, percentage differences, and more.
                    </flux:heading>
                </div>
            </div>
        </div>

        {{-- 1. X% of Y --}}
        <div x-data="xPercentOfY" class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
            <div class="mb-2 flex items-start justify-between gap-2">
                <flux:heading size="xl">What is <flux:badge>X%</flux:badge> of <flux:badge>Y</flux:badge>?</flux:heading>
                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="Explain the math" />
                    <flux:popover class="max-w-xs">
                        <flux:heading size="sm">How it works</flux:heading>
                        <p class="mt-2 text-sm">Multiply Y by X expressed as a decimal:</p>
                        <p class="mt-2 font-mono text-sm">result = Y × (X ÷ 100)</p>
                        <flux:separator class="my-3" />
                        <p class="text-sm">Example:</p>
                        <p class="mt-1 font-mono text-sm">1,000 × (50 ÷ 100) = 500</p>
                    </flux:popover>
                </flux:dropdown>
            </div>
            <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">Example: 50% of 1,000 = 500.</flux:subheading>

            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-3">
                <input type="number" inputmode="decimal" step="any" placeholder="X" aria-label="X" x-model.number="x" class="{{ $inputClass }}">
                <span class="{{ $opClass }}">%&nbsp;of</span>
                <input type="number" inputmode="decimal" step="any" placeholder="Y" aria-label="Y" x-model.number="y" class="{{ $inputClass }}">
                <span class="{{ $opClass }}">=</span>
                <output x-text="getResult()" class="{{ $outputClass }}">--</output>
            </div>
        </div>

        {{-- 2. X is what % of Y --}}
        <div x-data="xPercentageOfY" class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
            <div class="mb-2 flex items-start justify-between gap-2">
                <flux:heading size="xl"><flux:badge>X</flux:badge> is what % of <flux:badge>Y</flux:badge>?</flux:heading>
                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="Explain the math" />
                    <flux:popover class="max-w-xs">
                        <flux:heading size="sm">How it works</flux:heading>
                        <p class="mt-2 text-sm">Divide X by Y, then convert to a percentage:</p>
                        <p class="mt-2 font-mono text-sm">result = (X ÷ Y) × 100</p>
                        <flux:separator class="my-3" />
                        <p class="text-sm">Example:</p>
                        <p class="mt-1 font-mono text-sm">(50 ÷ 1,000) × 100 = 5%</p>
                    </flux:popover>
                </flux:dropdown>
            </div>
            <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">Example: 50 is 5% of 1,000.</flux:subheading>

            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-3">
                <input type="number" inputmode="decimal" step="any" placeholder="X" aria-label="X" x-model.number="x" class="{{ $inputClass }}">
                <span class="{{ $opClass }}">is</span>
                <output x-text="getResult()" class="{{ $outputClass }}">--</output>
                <span class="{{ $opClass }}">of</span>
                <input type="number" inputmode="decimal" step="any" placeholder="Y" aria-label="Y" x-model.number="y" class="{{ $inputClass }}">
            </div>
        </div>

        {{-- 3. % change from X to Y --}}
        <div x-data="percentageDifferenceOfXAndY" class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
            <div class="mb-2 flex items-start justify-between gap-2">
                <flux:heading size="xl">% change from <flux:badge>X</flux:badge> to <flux:badge>Y</flux:badge></flux:heading>
                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="Explain the math" />
                    <flux:popover class="max-w-xs">
                        <flux:heading size="sm">How it works</flux:heading>
                        <p class="mt-2 text-sm">Take the change (Y − X), divide by the starting value X, then convert to a percentage:</p>
                        <p class="mt-2 font-mono text-sm">result = ((Y − X) ÷ X) × 100</p>
                        <flux:separator class="my-3" />
                        <p class="text-sm">Example:</p>
                        <p class="mt-1 font-mono text-sm">((1,000 − 50) ÷ 50) × 100 = 1,900%</p>
                    </flux:popover>
                </flux:dropdown>
            </div>
            <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">
                <span class="block">Example 1: the difference from 50 to 1,000 is 1,900%.</span>
                <span class="block">Example 2: the difference from 100 to 200 is 100%.</span>
            </flux:subheading>

            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-3">
                <span class="{{ $opClass }}">From</span>
                <input type="number" inputmode="decimal" step="any" placeholder="X" aria-label="X" x-model.number="x" class="{{ $inputClass }}">
                <span class="{{ $opClass }}">to</span>
                <input type="number" inputmode="decimal" step="any" placeholder="Y" aria-label="Y" x-model.number="y" class="{{ $inputClass }}">
                <span class="{{ $opClass }}">=</span>
                <output x-text="getResult()" class="{{ $outputClass }}">--</output>
            </div>
        </div>

        {{-- 4. Y is X% of what? --}}
        <div x-data="yIsXPercentOfWhat" class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
            <div class="mb-2 flex items-start justify-between gap-2">
                <flux:heading size="xl"><flux:badge>Y</flux:badge> is <flux:badge>X%</flux:badge> of what?</flux:heading>
                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="Explain the math" />
                    <flux:popover class="max-w-xs">
                        <flux:heading size="sm">How it works</flux:heading>
                        <p class="mt-2 text-sm">Divide Y by X expressed as a decimal:</p>
                        <p class="mt-2 font-mono text-sm">result = Y ÷ (X ÷ 100)</p>
                        <flux:separator class="my-3" />
                        <p class="text-sm">Example:</p>
                        <p class="mt-1 font-mono text-sm">50 ÷ (25 ÷ 100) = 200</p>
                    </flux:popover>
                </flux:dropdown>
            </div>
            <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">Example: 50 is 25% of 200.</flux:subheading>

            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-3">
                <input type="number" inputmode="decimal" step="any" placeholder="Y" aria-label="Y" x-model.number="y" class="{{ $inputClass }}">
                <span class="{{ $opClass }}">is</span>
                <input type="number" inputmode="decimal" step="any" placeholder="X" aria-label="X" x-model.number="x" class="{{ $inputClass }}">
                <span class="{{ $opClass }}">%&nbsp;of</span>
                <output x-text="getResult()" class="{{ $outputClass }}">--</output>
            </div>
        </div>

        {{-- 5. X plus or minus Y% --}}
        <div x-data="xPlusOrMinusYPercent" class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
            <div class="mb-2 flex items-start justify-between gap-2">
                <flux:heading size="xl"><flux:badge>X</flux:badge> plus or minus <flux:badge>Y%</flux:badge></flux:heading>
                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="Explain the math" />
                    <flux:popover class="max-w-xs">
                        <flux:heading size="sm">How it works</flux:heading>
                        <p class="mt-2 text-sm">Add (or subtract) Y% of X to (or from) X:</p>
                        <p class="mt-2 font-mono text-sm">result = X ± X × (Y ÷ 100)</p>
                        <flux:separator class="my-3" />
                        <p class="text-sm">Useful for tax, tip, markup, and discount.</p>
                        <flux:separator class="my-3" />
                        <p class="text-sm">Example:</p>
                        <p class="mt-1 font-mono text-sm">100 + 10% = 110</p>
                        <p class="font-mono text-sm">100 − 10% = 90</p>
                    </flux:popover>
                </flux:dropdown>
            </div>
            <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">
                <span class="block">Example 1: 100 + 10% = 110.</span>
                <span class="block">Example 2: 100 − 10% = 90.</span>
            </flux:subheading>

            <div class="grid gap-3">
                <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-3">
                    <input type="number" inputmode="decimal" step="any" placeholder="X" aria-label="X" x-model.number="x" class="{{ $inputClass }}">
                    <span class="{{ $opClass }}">±</span>
                    <input type="number" inputmode="decimal" step="any" placeholder="Y" aria-label="Y" x-model.number="y" class="{{ $inputClass }}">
                    <span class="{{ $opClass }}">%</span>
                </div>
                <div class="mt-2 grid gap-2">
                    <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-2">
                        <span class="{{ $opClass }}">X + Y% =</span>
                        <output x-text="getAddResult()" class="{{ $outputClass }}">--</output>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-2">
                        <span class="{{ $opClass }}">X − Y% =</span>
                        <output x-text="getSubtractResult()" class="{{ $outputClass }}">--</output>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
