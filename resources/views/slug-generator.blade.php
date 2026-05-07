<x-layouts.app>

    <div class="mx-auto max-w-[1000px]" x-data="slugGenerator">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.link class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Slug Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Turn any string into a clean URL slug.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:textarea
                    name="text"
                    x-model="text"
                    label="Text"
                    placeholder="My Awesome Article Title!"
                    rows="3"
                />

                <div class="mt-6">
                    <div class="flex items-center gap-1">
                        <flux:label>Slug</flux:label>
                        <flux:dropdown position="bottom" align="start">
                            <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="What is a slug?" />
                            <flux:popover class="max-w-sm">
                                <flux:heading size="sm">What's a slug?</flux:heading>
                                <p class="mt-2 text-sm">The human-readable, URL-safe portion of a URL, usually derived from a title.</p>
                                <p class="mt-2 font-mono text-xs break-all">example.com/blog/<strong>my-awesome-post</strong></p>
                                <flux:separator class="my-3" />
                                <p class="text-sm">Slugs lowercase, transliterate accents, drop punctuation, and join words with a separator. They survive being copy-pasted, shared in chat, and indexed by search engines.</p>
                            </flux:popover>
                        </flux:dropdown>
                    </div>
                    <div class="mt-2 flex items-stretch gap-2">
                        <div
                            class="grow rounded-md border border-black/10 bg-zinc-50 px-4 py-3 font-mono text-base dark:border-white/10 dark:bg-zinc-900"
                            x-bind:class="{ 'opacity-40': !slug }"
                            x-text="slug || 'your-slug-appears-here'"
                        ></div>
                        <flux:button
                            x-on:click="copy()"
                            x-bind:disabled="!slug"
                            icon="document-duplicate"
                        >
                            <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                        </flux:button>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">Options</flux:heading>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <flux:select x-model="separator" label="Separator">
                        <flux:select.option value="-">- (hyphen)</flux:select.option>
                        <flux:select.option value="_">_ (underscore)</flux:select.option>
                        <flux:select.option value=".">. (dot)</flux:select.option>
                    </flux:select>
                    <flux:input
                        type="number"
                        min="0"
                        max="200"
                        step="1"
                        x-model.number="maxLength"
                        label="Max length"
                        description="0 = no limit"
                    />
                    <div class="flex flex-col gap-3">
                        <flux:label>Casing</flux:label>
                        <flux:checkbox x-model="lowercase" label="Lowercase" />
                    </div>
                    <div class="flex flex-col gap-3">
                        <flux:label>Filter</flux:label>
                        <flux:checkbox x-model="stripStopWords" label="Strip stop words" description="(the, and, of, …)" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-2" size="xl">Share</flux:heading>
                <flux:subheading class="mb-4">
                    The URL below carries your input and options.
                </flux:subheading>
                <p x-show="urlTooLong" x-cloak class="mb-4 text-sm text-amber-600 dark:text-amber-400">
                    Input is too long to include in the URL.
                </p>
                <flux:input type="url" x-model="url" readonly copyable label="Share URL" />
            </div>
        </div>
    </div>
</x-layouts.app>
