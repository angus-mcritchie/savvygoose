<x-layouts.app>

    <div
        class="mx-auto max-w-[1200px]"
        x-data="caseConverter"
        x-on:keydown.window.escape="clear()"
    >
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.language class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Case Converter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Convert text into camelCase, snake_case, kebab-case, and more.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:textarea
                    name="text"
                    x-model="text"
                    label="Input"
                    placeholder="hello world example phrase"
                    rows="3"
                />

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <flux:button x-on:click="clear()" x-bind:disabled="!text" icon="trash" size="sm" variant="filled">
                        Clear
                    </flux:button>
                    <flux:text size="sm" class="opacity-60">
                        Words separated by spaces, hyphens, underscores, dots, or case boundaries are all detected.
                    </flux:text>
                    <flux:dropdown position="bottom" align="end" class="ml-auto">
                        <flux:button icon="information-circle" variant="ghost" size="xs">
                            When to use which?
                        </flux:button>
                        <flux:popover class="max-w-sm">
                            <flux:heading size="sm">Common conventions</flux:heading>
                            <ul class="mt-2 space-y-1 text-sm">
                                <li><strong>camelCase</strong>: JS, Java, C# variables &amp; functions.</li>
                                <li><strong>PascalCase</strong>: classes &amp; types in most languages.</li>
                                <li><strong>snake_case</strong>: Python, Ruby, Rust, SQL, file names.</li>
                                <li><strong>SCREAMING_SNAKE_CASE</strong>: constants &amp; environment variables.</li>
                                <li><strong>kebab-case</strong>: URL slugs, CSS classes, CLI flags.</li>
                                <li><strong>Title Case</strong>: headings &amp; product names.</li>
                            </ul>
                        </flux:popover>
                    </flux:dropdown>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <template x-for="c in converters" :key="c.key">
                    <div
                        class="rounded-lg border border-black/10 p-6 transition dark:border-white/10"
                        x-bind:class="{ 'opacity-50': !text }"
                    >
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <flux:heading class="!font-bold" size="lg" x-text="c.label"></flux:heading>
                                <flux:subheading size="sm" x-text="c.description"></flux:subheading>
                            </div>
                            <x-copy-button
                                value="convert(c.key)"
                                flash="c.key"
                                icon="document-duplicate"
                                size="xs"
                                x-bind:disabled="!text"
                            />
                        </div>
                        <div
                            class="break-all rounded-md bg-zinc-100 p-3 font-mono text-sm dark:bg-zinc-900"
                            x-text="convert(c.key) || '...'"
                        ></div>
                    </div>
                </template>
            </div>

            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="The URL below carries your input. Anyone who opens it sees the same conversions."
                tooLongMessage="Input is too long to include in the URL."
            />
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
