<x-layouts.app>


    <form
        class="mx-auto max-w-[1200px]"
        x-data="barcode"
        x-on:submit.prevent="printBarcode"
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

        <link href="{{ Vite::asset('resources/css/barcode-generator.css') }}" rel="stylesheet" x-ref="stylesheet">

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
                    <flux:input name="value" x-model="value" label="Value" placeholder="value" />
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
                <flux:button class="w-full" type="submit" variant="primary">
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
    <x-tool-content />
</x-layouts.app>
