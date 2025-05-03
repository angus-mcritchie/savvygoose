<x-layouts.app>

    <div class="mx-auto max-w-[1200px]">

        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <img class="mx-auto w-[128px]" src="{{ asset('image/barcode.png') }}"width="128" height="128">
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">
                        Helpful Tools, Free Forever
                    </flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        A collection of free tools to help you with your daily tasks.
                    </flux:heading>
                </div>
            </div>
        </div>
        <div class="grid gap-8 lg:grid-cols-3">
            <flux:link class="!grid gap-8 rounded-lg border border-black/10 p-8 px-8 py-12 !no-underline transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-white/10" href="{{ route('barcode-generator') }}">
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
            <flux:link class="!grid gap-8 rounded-lg border border-black/10 p-8 px-8 py-12 !no-underline transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-white/10" href="{{ route('percentage-calculator') }}">
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
            <flux:link class="!grid gap-8 rounded-lg border border-black/10 p-8 px-8 py-12 !no-underline transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-white/10" href="{{ route('character-counter') }}">
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
