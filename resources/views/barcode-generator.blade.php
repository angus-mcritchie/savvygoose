<x-layouts.app>
    <h1 class="mb-8 text-center text-3xl font-bold text-neutral-900 dark:text-neutral-100">
        Barcode Generator
    </h1>


    <form class="mx-auto max-w-[1200px]" x-data="barcode" x-on:submit.prevent="printBarcode">
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
