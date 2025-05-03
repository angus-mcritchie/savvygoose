<x-layouts.app>
    <div class="grid gap-12">
        <div class="mx-auto max-w-[600px]" x-data="xPercentOfY">

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
                    <flux:heading class="mb-2" size="xl">What is <flux:badge>X%</flux:badge> of <flux:badge>Y</flux:badge>
                    </flux:heading>
                    <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">Example: 50% of 1,000 = 500.</flux:subheading>
                    <div class="grid gap-4 lg:grid-cols-3">
                        <flux:field>
                            <flux:label>X</flux:label>
                            <flux:input.group>
                                <flux:input type="number" x-model="x" />
                                <flux:input.group.suffix>%</flux:input.group.suffix>
                            </flux:input.group>
                        </flux:field>
                        <flux:field>
                            <flux:label>Y</flux:label>
                            <flux:input.group>
                                <flux:input type="number" x-model="y" />
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

        <div class="mx-auto max-w-[600px]" x-data="xPercentageOfY">
            <div class="grid gap-6">
                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <flux:heading class="mb-2" size="xl">
                        <flux:badge>X</flux:badge> is what percent of <flux:badge>Y</flux:badge>
                    </flux:heading>
                    <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">Example: 50 is 5% of 1,000.</flux:subheading>
                    <div class="grid gap-4 lg:grid-cols-3">
                        <flux:input name="x" type="number" x-model="x" label="X" />
                        <flux:input name="y" type="number" x-model="y" label="Y" />
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

        <div class="mx-auto max-w-[600px]" x-data="percentageDifferenceOfXAndY">
            <div class="grid gap-6">
                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <flux:heading class="mb-2" size="xl">What is the % increase/decrease from <flux:badge>X</flux:badge> to <flux:badge>Y</flux:badge>
                    </flux:heading>
                    <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">
                        Example 1: the difference from 50 to 1,000 is 1,900%.<br>
                        Example 2: the difference from 100 to 200 is 100%.
                    </flux:subheading>
                    <div class="grid gap-4 lg:grid-cols-3">
                        <flux:input name="x" type="number" x-model="x" label="X" />
                        <flux:input name="y" type="number" x-model="y" label="Y" />
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
