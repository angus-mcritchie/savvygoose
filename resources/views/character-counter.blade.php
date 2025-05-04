<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="characterCounter">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <img class="mx-auto w-[128px]" src="{{ asset('image/keyboard.png') }}" width="128" height="128">
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Character Counter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Count characters, words, and lines in your text.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Count characters, words & lines</flux:heading>
                <div class="mb-8 grid gap-4">
                    <flux:textarea name="text" x-model="text" label="Text to count" placeholder="Type or paste text here" />
                </div>

                <div class="grid gap-8 lg:grid-cols-3">
                    <div class="rounded-lg border border-black/10 p-8 text-center dark:border-white/10">
                        <flux:subheading class="mb-4" size="xl">Characters</flux:subheading>
                        <flux:heading class="!text-6xl" x-text="getCharacterCount()">0</flux:heading>
                    </div>
                    <div class="rounded-lg border border-black/10 p-8 text-center dark:border-white/10">
                        <flux:subheading class="mb-4" size="xl">Words</flux:subheading>
                        <flux:heading class="!text-6xl" x-text="getWordCount()">0</flux:heading>
                    </div>
                    <div class="rounded-lg border border-black/10 p-8 text-center dark:border-white/10">
                        <flux:subheading class="mb-4" size="xl">Lines</flux:subheading>
                        <flux:heading class="!text-6xl" x-text="getLineCount()">0</flux:heading>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
