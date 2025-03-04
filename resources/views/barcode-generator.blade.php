<x-layouts.app>
    <h1 class="mb-8 text-center text-3xl font-bold text-neutral-900 dark:text-neutral-100">
        Barcode Generator
    </h1>


    <div class="mx-auto max-w-[1200px]" x-data="{ label: null, value: null }">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">1. Generate</flux:heading>
                <div class="grid gap-4">
                    <flux:input x-model="label" label="Label" name="label" placeholder="my label" />
                    <flux:input x-model="value" label="Value" name="value" placeholder="my value" />
                </div>
            </div>
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">2. Preview</flux:heading>
                <div class="mb-8 grid gap-4">
                    <img class="mx-auto w-20" src="{{ asset('image/barcode.png') }}"width="128" height="128">
                </div>
                <flux:button class="w-full" variant="primary">
                    Print
                </flux:button>
            </div>
            <div class="col-span-2 rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">3. Share</flux:heading>
                <flux:subheading>
                    You can link to this page with url parameters.

                    You can link to this page with url parameters.
                    Example: http://savvygoose.com/barcode-generator?value=12345&label=mylabel&print=true

                    Test the example link above.
                </flux:subheading>
            </div>
        </div>

    </div>
</x-layouts.app>
