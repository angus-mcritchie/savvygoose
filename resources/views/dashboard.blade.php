<x-layouts.app>
    <h1 class="mb-8 text-center text-3xl font-bold text-neutral-900 dark:text-neutral-100">
        Helpful apps, for free
    </h1>

    <div class="mx-auto max-w-[1200px]">
        <div class="grid gap-8 lg:grid-cols-3">
            <flux:link class="!grid gap-8 rounded-lg border border-white/10 px-8 py-12 !no-underline transition duration-300 hover:-translate-y-1 hover:shadow-xl" href="{{ route('barcode-generator') }}">
                <img class="size-20" src="{{ asset('image/barcode.png') }}" width="128" height="128" />

                <div>
                    <flux:heading class="!text-xl !font-bold">
                        Barcode Generator
                    </flux:heading>
                    <flux:subheading>
                        Generates & print 128 barcodes.
                    </flux:subheading>
                </div>
            </flux:link>
            <flux:link class="!grid gap-8 rounded-lg border border-white/10 px-8 py-12 !no-underline transition duration-300 hover:-translate-y-1 hover:shadow-xl" href="{{ route('percentage-calculator') }}">
                <img class="size-20" src="{{ asset('image/discount.png') }}" width="128" height="128" />

                <div>
                    <flux:heading class="!text-xl !font-bold">
                        Percentage Calculator
                    </flux:heading>
                    <flux:subheading>
                        Common percentage calculations.
                    </flux:subheading>
                </div>
            </flux:link>
            <flux:link class="!grid gap-8 rounded-lg border border-white/10 px-8 py-12 !no-underline transition duration-300 hover:-translate-y-1 hover:shadow-xl" href="{{ route('character-counter') }}">
                <img class="size-20" src="{{ asset('image/keyboard.png') }}" width="128" height="128" />

                <div>
                    <flux:heading class="!text-xl !font-bold">
                        Character Counter
                    </flux:heading>
                    <flux:subheading>
                        Count characters & words.
                    </flux:subheading>
                </div>
            </flux:link>
        </div>
    </div>
</x-layouts.app>
