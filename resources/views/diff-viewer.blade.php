<x-layouts.app>

    <div class="mx-auto max-w-[1400px]" x-data="diffViewer">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.arrows-right-left class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Diff Viewer</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Compare two pieces of text by line, word, or unified.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="xl">1. Inputs</flux:heading>

                <div class="grid gap-6 lg:grid-cols-2">
                    <flux:textarea
                        name="original"
                        x-model="original"
                        label="Original"
                        placeholder="Paste the original text here…"
                        rows="12"
                        class="font-mono"
                    />
                    <flux:textarea
                        name="modified"
                        x-model="modified"
                        label="Modified"
                        placeholder="Paste the modified text here…"
                        rows="12"
                        class="font-mono"
                    />
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <flux:button x-on:click="swap()" x-bind:disabled="!hasContent" icon="arrows-right-left" size="sm">
                        Swap
                    </flux:button>
                    <flux:button x-on:click="clear()" x-bind:disabled="!hasContent" icon="trash" size="sm" variant="filled">
                        Clear
                    </flux:button>
                    <flux:checkbox x-model="ignoreWhitespace" label="Ignore whitespace" />
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-black/10 pb-4 dark:border-white/10">
                    <div class="flex items-center gap-1">
                        <flux:heading size="xl">2. Diff</flux:heading>
                        <flux:dropdown position="bottom" align="start">
                            <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="Diff modes" />
                            <flux:popover class="max-w-sm">
                                <flux:heading size="sm">Three ways to view a diff</flux:heading>
                                <ul class="mt-2 space-y-2 text-sm">
                                    <li><strong>Side-by-side</strong>: original on the left, modified on the right. Best when there are many concurrent edits.</li>
                                    <li><strong>Unified</strong>: one column with <code class="font-mono">+</code>/<code class="font-mono">-</code> markers (like <code class="font-mono">git diff</code>). Best for code review.</li>
                                    <li><strong>Word</strong>: inline highlighting of changed words. Best for prose &amp; copy edits.</li>
                                </ul>
                            </flux:popover>
                        </flux:dropdown>
                    </div>
                    <flux:radio.group x-model="mode" variant="segmented" size="sm">
                        <flux:radio value="side-by-side" label="Side-by-side" />
                        <flux:radio value="unified" label="Unified" />
                        <flux:radio value="word" label="Word" />
                    </flux:radio.group>
                </div>

                <template x-if="!hasContent">
                    <div class="flex flex-col items-center justify-center gap-2 py-10 text-center text-zinc-500 dark:text-zinc-400">
                        <flux:icon.document-text class="size-8 opacity-50" />
                        <flux:text>Paste text into both inputs to see the diff.</flux:text>
                    </div>
                </template>

                <template x-if="hasContent && !hasChanges">
                    <div class="flex flex-col items-center justify-center gap-2 py-10 text-center text-emerald-600 dark:text-emerald-400">
                        <flux:icon.check-circle class="size-8" />
                        <flux:text>Identical. No differences detected.</flux:text>
                    </div>
                </template>

                <template x-if="hasContent && hasChanges">
                    <div>
                        <div class="mb-4 flex flex-wrap gap-2 text-sm">
                            <span class="rounded bg-emerald-500/10 px-2 py-1 font-mono text-emerald-700 dark:text-emerald-300">
                                + <span x-text="stats.added"></span> added
                            </span>
                            <span class="rounded bg-red-500/10 px-2 py-1 font-mono text-red-700 dark:text-red-300">
                                − <span x-text="stats.removed"></span> removed
                            </span>
                            <span class="rounded bg-zinc-500/10 px-2 py-1 font-mono text-zinc-700 dark:text-zinc-300">
                                = <span x-text="stats.unchanged"></span> unchanged
                            </span>
                        </div>

                        <template x-if="mode === 'side-by-side'">
                            <div class="grid grid-cols-2 overflow-x-auto rounded-md border border-black/10 font-mono text-sm dark:border-white/10">
                                <div class="border-r border-black/10 dark:border-white/10">
                                    <div class="border-b border-black/10 bg-zinc-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide opacity-70 dark:border-white/10 dark:bg-zinc-900">Original</div>
                                    <template x-for="(row, i) in sideBySide.left" :key="'l-' + i">
                                        <div
                                            class="grid grid-cols-[3rem_1fr] items-start"
                                            :class="{
                                                'bg-red-500/10': row.type === 'removed',
                                                'bg-zinc-500/5': row.type === 'empty',
                                            }"
                                        >
                                            <span class="select-none px-2 py-0.5 text-right text-xs opacity-50" x-text="row.num ?? ''"></span>
                                            <span class="whitespace-pre-wrap break-all px-2 py-0.5" x-text="row.text"></span>
                                        </div>
                                    </template>
                                </div>
                                <div>
                                    <div class="border-b border-black/10 bg-zinc-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide opacity-70 dark:border-white/10 dark:bg-zinc-900">Modified</div>
                                    <template x-for="(row, i) in sideBySide.right" :key="'r-' + i">
                                        <div
                                            class="grid grid-cols-[3rem_1fr] items-start"
                                            :class="{
                                                'bg-emerald-500/10': row.type === 'added',
                                                'bg-zinc-500/5': row.type === 'empty',
                                            }"
                                        >
                                            <span class="select-none px-2 py-0.5 text-right text-xs opacity-50" x-text="row.num ?? ''"></span>
                                            <span class="whitespace-pre-wrap break-all px-2 py-0.5" x-text="row.text"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="mode === 'unified'">
                            <div class="overflow-x-auto rounded-md border border-black/10 font-mono text-sm dark:border-white/10">
                                <template x-for="(row, i) in unified" :key="'u-' + i">
                                    <div
                                        class="grid grid-cols-[2.5rem_2.5rem_1.5rem_1fr] items-start"
                                        :class="{
                                            'bg-emerald-500/10': row.type === 'added',
                                            'bg-red-500/10': row.type === 'removed',
                                        }"
                                    >
                                        <span class="select-none px-2 py-0.5 text-right text-xs opacity-50" x-text="row.leftNum ?? ''"></span>
                                        <span class="select-none px-2 py-0.5 text-right text-xs opacity-50" x-text="row.rightNum ?? ''"></span>
                                        <span class="select-none text-center text-xs opacity-70" x-text="row.type === 'added' ? '+' : row.type === 'removed' ? '-' : ' '"></span>
                                        <span class="whitespace-pre-wrap break-all px-2 py-0.5" x-text="row.text"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="mode === 'word'">
                            <div class="rounded-md border border-black/10 p-4 font-mono text-sm leading-relaxed dark:border-white/10">
                                <template x-for="(part, i) in wordDiff" :key="'w-' + i">
                                    <span
                                        class="whitespace-pre-wrap"
                                        :class="{
                                            'bg-emerald-500/20 text-emerald-800 dark:text-emerald-200': part.type === 'added',
                                            'bg-red-500/20 text-red-800 line-through dark:text-red-200': part.type === 'removed',
                                        }"
                                        x-text="part.text"
                                    ></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:heading class="mb-2" size="xl">Share</flux:heading>
                <flux:subheading class="mb-4">
                    The URL below carries both inputs and the view mode.
                </flux:subheading>
                <p x-show="urlTooLong" x-cloak class="mb-4 text-sm text-amber-600 dark:text-amber-400">
                    Inputs are too long to include in the URL.
                </p>
                <flux:input type="url" x-model="url" readonly copyable label="Share URL" />
            </div>
        </div>
    </div>
</x-layouts.app>
