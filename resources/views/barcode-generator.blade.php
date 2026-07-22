<x-layouts.app>

    @push('head')
        @vite('resources/css/barcode-generator.css')
    @endpush

    <form
        class="mx-auto max-w-[1200px]"
        x-data="barcode"
        x-on:submit.prevent="printBarcode()"
        x-on:keydown.window.cmd.enter.prevent="printBarcode()"
        x-on:keydown.window.ctrl.enter.prevent="printBarcode()"
    >

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.tag class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Barcode Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">Generate Code 128 barcodes and print them straight from your browser.</flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex items-center justify-between gap-2 border-b border-black/10 pb-4 dark:border-white/10">
                    <flux:heading size="xl">1. Generate</flux:heading>
                    <flux:dropdown position="bottom" align="end">
                        <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="About Code 128" />
                        <flux:popover class="max-w-sm">
                            <flux:heading size="sm">Code 128</flux:heading>
                            <p class="mt-2 text-sm">A high-density 1D barcode that encodes the full ASCII set: letters, digits, and common symbols.</p>
                            <flux:separator class="my-3" />
                            <ul class="space-y-1 text-sm">
                                <li><strong>Label</strong>: printed above the bars; purely human-readable.</li>
                                <li><strong>Value</strong>: what the scanner returns. Used for the bars and printed below.</li>
                            </ul>
                            <flux:separator class="my-3" />
                            <p class="text-sm">Used widely in shipping, inventory, and warehousing. Stick to printable ASCII for best scanner support.</p>
                        </flux:popover>
                    </flux:dropdown>
                </div>
                <div class="grid gap-8">
                    <flux:input name="label" x-model="label" label="Label" placeholder="my label" />
                    <div class="grid gap-2">
                        <flux:input name="value" x-model="value" label="Value" placeholder="value" />
                        <p x-show="error" x-cloak x-text="error" class="text-sm text-red-600 dark:text-red-400"></p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">2. Preview</flux:heading>
                <div class="mb-6 grid gap-4">
                    <flux:subheading class="text-center !font-normal" size="lg">
                        Here's your barcode.
                    </flux:subheading>
                    <div data-barcode-canvas x-ref="barcodeCanvas" :style="canvasStyle()">
                        <div data-barcode-paper>
                            <div data-barcode>
                                <div data-barcode-label x-show="showLabel"><span x-text="getLabel()">my label</span></div>
                                <div data-barcode-code><span style="font-family: 'Libre Barcode 128';" x-text="getCode()">ÌvalueÈÎ</span></div>
                                <div data-barcode-value x-show="showValue"><span x-text="getValue()">value</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <flux:button class="w-full" type="submit" variant="primary" x-bind:disabled="!!error">
                    Print
                </flux:button>
            </div>
            <div class="rounded-lg border border-black/10 p-8 lg:col-span-2 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Customize</flux:heading>
                <flux:subheading class="mb-6">
                    Tweak the sticker size and visibility.
                </flux:subheading>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <flux:input
                        type="number"
                        step="0.1"
                        min="0.5"
                        x-model.number="width"
                        label="Sticker width (cm)"
                    />
                    <flux:input
                        type="number"
                        step="0.1"
                        min="0.5"
                        x-model.number="height"
                        label="Sticker height (cm)"
                    />
                    <flux:input
                        type="number"
                        step="0.05"
                        min="0.05"
                        x-model.number="labelSize"
                        label="Label font size (cm)"
                    />
                    <flux:input
                        type="number"
                        step="0.05"
                        min="0.05"
                        x-model.number="codeSize"
                        label="Barcode font size (cm)"
                    />
                    <flux:input
                        type="number"
                        step="0.05"
                        min="0.05"
                        x-model.number="valueSize"
                        label="Value font size (cm)"
                    />
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <flux:checkbox x-model="showLabel" label="Show label" />
                    <flux:checkbox x-model="showValue" label="Show value" />
                </div>
            </div>
            <div class="rounded-lg border border-black/10 p-8 lg:col-span-2 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">4. Share</flux:heading>
                <x-share-field
                    :heading="false"
                    subheading="You can link to this page with url parameters."
                >
                    <div class="mt-4">
                        <flux:checkbox x-model="print" label="Print when opening link" />
                    </div>
                </x-share-field>
            </div>
        </div>
    </form>

    <section class="mx-auto mt-12 grid max-w-3xl gap-10 leading-relaxed text-zinc-700 dark:text-zinc-300">
        <div>
            <flux:heading level="2" class="!mb-4 !text-2xl !font-semibold tracking-tight">What is a Code 128 barcode?</flux:heading>
            <p class="mb-3">
                Code 128 is a high-density 1D (linear) barcode designed to encode the full ASCII character set:
                uppercase and lowercase letters, digits, and common punctuation. It is one of the most widely used
                symbologies in shipping labels, inventory tags, and warehouse systems because it fits a lot of data
                into a small width and includes a check digit for reliable scanning.
            </p>
            <p>
                This generator turns whatever you type into a scannable Code 128 barcode right in your browser. Type
                text or a number, adjust the sticker size, and print it or share the link. Nothing is uploaded.
            </p>
        </div>

        <div>
            <flux:heading level="2" class="!mb-4 !text-2xl !font-semibold tracking-tight">Code 128 subsets</flux:heading>
            <p class="mb-4">Code 128 has three character subsets. The encoder switches between them automatically to keep the barcode as short as possible.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-black/10 dark:border-white/10">
                        <tr>
                            <th class="py-2 pr-4 font-semibold">Subset</th>
                            <th class="py-2 pr-4 font-semibold">Covers</th>
                            <th class="py-2 font-semibold">Best for</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/10">
                        <tr><td class="py-2 pr-4 font-mono">A</td><td class="py-2 pr-4">Uppercase, digits, control characters</td><td class="py-2">Legacy systems needing control codes</td></tr>
                        <tr><td class="py-2 pr-4 font-mono">B</td><td class="py-2 pr-4">Upper and lowercase, digits, symbols</td><td class="py-2">General text and mixed-case values</td></tr>
                        <tr><td class="py-2 pr-4 font-mono">C</td><td class="py-2 pr-4">Pairs of digits (00–99)</td><td class="py-2">Long numeric codes, at double density</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <flux:heading level="2" class="!mb-4 !text-2xl !font-semibold tracking-tight">Code 128 vs other barcodes</flux:heading>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-black/10 dark:border-white/10">
                        <tr>
                            <th class="py-2 pr-4 font-semibold">Symbology</th>
                            <th class="py-2 pr-4 font-semibold">Encodes</th>
                            <th class="py-2 font-semibold">Typical use</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/10">
                        <tr><td class="py-2 pr-4 font-medium">Code 128</td><td class="py-2 pr-4">Full ASCII, any length</td><td class="py-2">Shipping, inventory, logistics</td></tr>
                        <tr><td class="py-2 pr-4 font-medium">Code 39</td><td class="py-2 pr-4">Uppercase, digits, few symbols</td><td class="py-2">Older industrial and ID systems</td></tr>
                        <tr><td class="py-2 pr-4 font-medium">EAN-13 / UPC-A</td><td class="py-2 pr-4">Fixed-length product numbers</td><td class="py-2">Retail products at point of sale</td></tr>
                        <tr><td class="py-2 pr-4 font-medium"><a href="{{ route('qr-code-generator') }}" wire:navigate class="underline underline-offset-4">QR code</a></td><td class="py-2 pr-4">2D: URLs, text, Wi-Fi, contacts</td><td class="py-2">Phone-camera scanning, links</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <flux:heading level="2" class="!mb-4 !text-2xl !font-semibold tracking-tight">Printing and scanning checklist</flux:heading>
            <ul class="ml-5 grid list-disc gap-2">
                <li>Leave a clear quiet zone (blank margin) on both sides of the bars.</li>
                <li>Print solid black on a white background for maximum contrast.</li>
                <li>Print at 300 DPI or higher so thin bars stay sharp.</li>
                <li>If a scanner struggles, make the sticker wider rather than smaller.</li>
                <li>Stick to printable ASCII; accented or non-Latin characters can't be encoded in Code 128.</li>
            </ul>
        </div>
    </section>

    <x-tool-content />
</x-layouts.app>
