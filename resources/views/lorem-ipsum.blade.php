<x-layouts.app>

    <div
        class="mx-auto max-w-[1000px]"
        x-data="loremIpsum"
        x-on:keydown.window.cmd.enter.prevent="regenerate()"
        x-on:keydown.window.ctrl.enter.prevent="regenerate()"
    >
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.document-text class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Lorem Ipsum</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Generate placeholder text: paragraphs, sentences, or words.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex items-center justify-between gap-2 border-b border-black/10 pb-4 dark:border-white/10">
                    <flux:heading size="xl">Options</flux:heading>
                    <flux:dropdown position="bottom" align="end">
                        <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="What is lorem ipsum?" />
                        <flux:popover class="max-w-sm">
                            <flux:heading size="sm">Why lorem ipsum?</flux:heading>
                            <p class="mt-2 text-sm">Garbled Latin from Cicero's <em>De finibus bonorum et malorum</em> (45 BC), used as filler since the 1500s.</p>
                            <flux:separator class="my-3" />
                            <p class="text-sm">It's nonsense by design. Designers use it so reviewers focus on layout and typography rather than reading the copy.</p>
                            <flux:separator class="my-3" />
                            <p class="text-sm">The classic opener "Lorem ipsum dolor sit amet…" is genuinely from Cicero, just chopped mid-word.</p>
                        </flux:popover>
                    </flux:dropdown>
                </div>

                <div class="grid gap-6 sm:grid-cols-[1fr_auto] sm:items-end">
                    <flux:radio.group x-model="type" variant="segmented" label="Generate">
                        <flux:radio value="paragraphs" label="Paragraphs" />
                        <flux:radio value="sentences" label="Sentences" />
                        <flux:radio value="words" label="Words" />
                    </flux:radio.group>
                    <flux:input
                        type="number"
                        min="1"
                        max="100"
                        step="1"
                        x-model.number="count"
                        label="Count"
                        class="sm:max-w-[120px]"
                    />
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <flux:checkbox
                        x-model="classic"
                        label="Start with “Lorem ipsum dolor sit amet…”"
                    />
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <flux:heading size="xl">Output</flux:heading>
                    <div class="flex gap-2">
                        <flux:button x-on:click="regenerate()" icon="arrow-path" size="sm">
                            Regenerate
                        </flux:button>
                        <flux:button
                            x-on:click="$download(output, `lorem-${type}-${count}.txt`)"
                            x-bind:disabled="!output"
                            icon="arrow-down-tray"
                            size="sm"
                        >
                            .txt
                        </flux:button>
                        <x-copy-button
                            value="output"
                            flash="'lorem'"
                            icon="document-duplicate"
                            size="sm"
                            variant="primary"
                            x-bind:disabled="!output"
                        />
                    </div>
                </div>

                <div
                    class="whitespace-pre-wrap rounded-md bg-zinc-50 p-5 leading-relaxed dark:bg-zinc-900"
                    x-text="output"
                ></div>
            </div>

            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="The URL below carries the type, count, and seed. It always reproduces the same text."
            />
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
