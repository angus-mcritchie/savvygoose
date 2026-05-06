<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="characterCounter">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <img class="mx-auto w-[128px]" src="{{ asset('image/keyboard.png') }}" alt="Keyboard" width="128" height="128">
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
                <div class="mb-4 grid gap-4">
                    <flux:textarea name="text" x-model="text" label="Text" placeholder="Type or paste text here" rows="6" />
                </div>

                <div class="mb-8 flex gap-2">
                    <flux:button x-on:click="copy()" x-bind:disabled="!text" icon="document-duplicate" size="sm">
                        <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                    </flux:button>
                    <flux:button x-on:click="clear()" x-bind:disabled="!text" icon="trash" size="sm" variant="filled">
                        Clear
                    </flux:button>
                </div>

                <div class="grid gap-4 transition-opacity lg:grid-cols-3" x-bind:class="{ 'opacity-50': !text }">
                    <x-stat label="Characters" value="characterCount" />
                    <x-stat label="Characters (no spaces)" value="characterCountNoSpaces" />
                    <x-stat label="Words" value="wordCount" />
                    <x-stat label="Sentences" value="sentenceCount" />
                    <x-stat label="Lines" value="lineCount" />
                    <x-stat label="Avg word length" value="averageWordLength" />
                    <x-stat label="Reading time" value="readingTime" placeholder="0 min" class="lg:col-span-3" />
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
