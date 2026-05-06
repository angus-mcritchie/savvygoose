<x-layouts.app>
    <div class="mx-auto grid max-w-[600px] gap-12">
        <div x-data="xPercentOfY">

            <div class="mb-8 flex justify-center">
                <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                    <img class="mx-auto w-[128px]" src="{{ asset('image/discount.png') }}"width="128" height="128">
                    <div>
                        <flux:heading class="mb-1" level="1" size="xl">Percentage Calculator</flux:heading>
                        <flux:heading class="font-normal opacity-70" level="2">
                            Calculate percentages, percentage differences, and more.
                        </flux:heading>
                    </div>
                </div>
            </div>

            <div class="grid gap-6">
                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <flux:heading size="xl">What is <flux:badge>X%</flux:badge> of <flux:badge>Y</flux:badge></flux:heading>
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
                    <div class="grid gap-4 lg:grid-cols-3">
                        <flux:field>
                            <flux:label>X</flux:label>
                            <flux:input.group>
                                <flux:input type="number" inputmode="decimal" step="any" x-model.number="x" />
                                <flux:input.group.suffix>%</flux:input.group.suffix>
                            </flux:input.group>
                        </flux:field>
                        <flux:field>
                            <flux:label>Y</flux:label>
                            <flux:input.group>
                                <flux:input type="number" inputmode="decimal" step="any" x-model.number="y" />
                            </flux:input.group>
                        </flux:field>
                        <flux:field>
                            <flux:label>Result</flux:label>
                            <flux:input.group>
                                <flux:input readonly copyable x-bind:value="getResult()" />
                            </flux:input.group>
                        </flux:field>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="xPercentageOfY">
            <div class="grid gap-6">
                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <flux:heading size="xl"><flux:badge>X</flux:badge> is what percent of <flux:badge>Y</flux:badge></flux:heading>
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
                    <div class="grid gap-4 lg:grid-cols-3">
                        <flux:input name="x" type="number" inputmode="decimal" step="any" x-model.number="x" label="X" />
                        <flux:input name="y" type="number" inputmode="decimal" step="any" x-model.number="y" label="Y" />
                        <flux:input
                            name="result"
                            type="text"
                            copyable
                            readonly
                            x-bind:value="getResult()"
                            label="Result"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div x-data="percentageDifferenceOfXAndY">
            <div class="grid gap-6">
                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <flux:heading size="xl">What is the % increase/decrease from <flux:badge>X</flux:badge> to <flux:badge>Y</flux:badge></flux:heading>
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
                    <div class="grid gap-4 lg:grid-cols-3">
                        <flux:input name="x" type="number" inputmode="decimal" step="any" x-model.number="x" label="X" />
                        <flux:input name="y" type="number" inputmode="decimal" step="any" x-model.number="y" label="Y" />
                        <flux:input
                            name="result"
                            type="text"
                            copyable
                            readonly
                            x-bind:value="getResult()"
                            label="Result"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
