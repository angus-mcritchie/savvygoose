<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="characterCounter" x-on:keydown.window.escape="clear()">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.hashtag class="size-20 text-zinc-700 dark:text-zinc-200" />
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
                    <x-copy-button
                        value="text"
                        flash="'cc-text'"
                        icon="document-duplicate"
                        size="sm"
                        x-bind:disabled="!text"
                    />
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

                <div class="mt-4 flex justify-end">
                    <flux:dropdown position="bottom" align="end">
                        <flux:button icon="information-circle" variant="ghost" size="xs">
                            How are these counted?
                        </flux:button>
                        <flux:popover class="max-w-sm">
                            <flux:heading size="sm">How each stat is counted</flux:heading>
                            <ul class="mt-2 space-y-2 text-sm">
                                <li><strong>Characters</strong>: every Unicode code point, including spaces &amp; line breaks.</li>
                                <li><strong>Words</strong>: runs of non-whitespace separated by spaces or punctuation.</li>
                                <li><strong>Sentences</strong>: segments terminated by <code class="font-mono">.</code>, <code class="font-mono">!</code>, or <code class="font-mono">?</code>.</li>
                                <li><strong>Reading time</strong>: based on 200 words per minute (average adult silent reading).</li>
                            </ul>
                        </flux:popover>
                    </flux:dropdown>
                </div>
            </div>

            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="The URL below carries your text."
                tooLongMessage="Text is too long to include in the URL."
            />
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
