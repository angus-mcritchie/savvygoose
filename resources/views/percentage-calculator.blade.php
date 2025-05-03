<x-layouts.app>
    <h1 class="mb-8 text-center text-3xl font-bold text-neutral-900 dark:text-neutral-100">
        Percentage Calculator
    </h1>
    <div class="grid gap-12">

        <div class="mx-auto max-w-[600px]" x-data="xPercentOfY">
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
                        <flux:input type="number" x-model="x" label="X" name="x" />
                        <flux:input type="number" x-model="y" label="Y" name="y" />
                        <flux:input copyable type="text" readonly x-bind:value="getResult()" label="Result" name="result" />
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
                        <flux:input type="number" x-model="x" label="X" name="x" />
                        <flux:input type="number" x-model="y" label="Y" name="y" />
                        <flux:input copyable type="text" readonly x-bind:value="getResult()" label="Result" name="result" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
