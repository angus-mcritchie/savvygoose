<x-layouts.app>
    <div
        class="mx-auto max-w-[1000px]"
        x-data="atrekRcHeaderCard"
        x-on:keydown.window.cmd.enter.prevent="printSticker()"
        x-on:keydown.window.ctrl.enter.prevent="printSticker()"
    >
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.tag class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Atrek RC header card sticker</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">94 × 12 mm sticker for the pre-printed header card.</flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Fields</flux:heading>
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:input x-model="sku" label="SKU" placeholder="1234" />
                    <flux:input x-model="barcode" label="Barcode" placeholder="1234567890123" inputmode="numeric" maxlength="13" />
                </div>
                <div class="mt-6">
                    <flux:input x-model="name" label="Product name" placeholder="Example Product Title Only 2Pcs" />
                </div>
                <p class="mt-4 text-sm text-red-600 dark:text-red-400" x-show="error" x-text="error" x-cloak></p>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Preview</flux:heading>

                <div class="header-card-preview flex justify-center py-6">
                    <div class="header-card-scale-wrapper">
                        <div class="sticker" x-ref="sticker">
                            <div class="left">
                                <div class="sku" x-text="sku"></div>
                                <div class="name" x-text="name"></div>
                            </div>
                            <div class="right">
                                <svg x-ref="barcodeSvg"></svg>
                                <div class="barcode" x-text="barcode"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-center">
                    <flux:button x-on:click="printSticker()" variant="primary" icon="printer">Print sticker</flux:button>
                </div>
            </div>

            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="Bookmark or share this URL to reopen with these values."
                tooLongMessage="Input is too long to include in the URL."
            />
        </div>
    </div>

    <style>
        .header-card-preview .header-card-scale-wrapper {
            width: calc(94mm * 2.5);
            height: calc(12mm * 2.5);
            max-width: 100%;
        }
        .header-card-preview .sticker {
            transform: scale(2.5);
            transform-origin: top left;
            width: 94mm;
            height: 12mm;
            background: white;
            color: black;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            padding: 1mm 3mm;
            gap: 3mm;
            font-family: "Inter", system-ui, sans-serif;
            box-sizing: border-box;
            border-radius: 1mm;
        }
        .header-card-preview .sticker .left { min-width: 0; }
        .header-card-preview .sticker .left .sku { font-weight: 700; font-size: 4mm; line-height: 1; color: black; }
        .header-card-preview .sticker .left .name {
            font-weight: 400; font-size: 2.5mm; line-height: 1.05; margin-top: 0.7mm; color: black;
            display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-clamp: 2;
            overflow: hidden; overflow-wrap: break-word; word-break: break-word;
        }
        .header-card-preview .sticker .right { display: flex; flex-direction: column; align-items: center; gap: 0.3mm; margin-top: 1mm; }
        .header-card-preview .sticker .right svg { display: block; height: 5mm; width: auto; }
        .header-card-preview .sticker .right .barcode { font-size: 1.8mm; letter-spacing: 0.05em; color: black; text-align: center; }
    </style>
</x-layouts.app>
