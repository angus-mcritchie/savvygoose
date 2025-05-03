<x-layouts.app>


    <form class="mx-auto max-w-[1200px]" x-data="barcode" x-on:submit.prevent="printBarcode">

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <img class="mx-auto w-[128px]" src="{{ asset('image/barcode.png') }}"width="128" height="128">
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Barcode Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">Generate and print code 128 barcodes in seconds.</flux:heading>
                </div>
            </div>
        </div>

        <link href="{{ Vite::asset('resources/css/barcode-generator.css') }}" rel="stylesheet" x-ref="stylesheet">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">1. Generate</flux:heading>
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
                    <div data-barcode-canvas x-ref="barcodeCanvas">
                        <div data-barcode-paper>
                            <div data-barcode>
                                <div data-barcode-label><span x-text="getLabel()">my label</span></div>
                                <div data-barcode-code><span style="font-family: 'Libre Barcode 128';" x-text="getCode()">ÌvalueÈÎ</span></div>
                                <div data-barcode-value><span x-text="getValue()">value</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <flux:button class="w-full cursor-pointer" type="submit" variant="primary">
                    Print
                </flux:button>
            </div>
            <div class="rounded-lg border border-black/10 p-8 lg:col-span-2 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Share</flux:heading>
                <flux:subheading class="mb-2">
                    You can link to this page with url parameters.
                </flux:subheading>

                <div class="grid gap-4">
                    <flux:input
                        type="url"
                        x-model="url"
                        readonly
                        x-model="url"
                        copyable
                        label="Share URL"
                    />
                    <flux:checkbox x-model="print" label="Print when open link" />
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
