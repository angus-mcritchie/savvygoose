<x-layouts.app>

    <div
        class="mx-auto max-w-[1200px]"
        x-data="mermaidEditor"
        x-on:keydown.window.escape="resetView()"
    >
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.rectangle-group class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Mermaid Editor</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Write Mermaid, preview live, and export SVG or PNG.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div
                x-ref="workspace"
                class="gap-6"
                x-bind:class="isFullscreen
                    ? 'flex h-full overflow-hidden bg-zinc-50 p-4 dark:bg-zinc-950'
                    : 'grid lg:grid-cols-2'"
            >
                {{-- Editor --}}
                <div
                    x-show="!isFullscreen || showEditor"
                    class="flex flex-col rounded-lg border border-black/10 p-8 dark:border-white/10"
                    x-bind:class="isFullscreen && 'h-full w-2/5 max-w-[560px] shrink-0 overflow-y-auto'"
                >
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-2 border-b border-black/10 pb-4 dark:border-white/10">
                        <flux:heading size="xl">Code</flux:heading>
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:select
                                size="sm"
                                x-on:change="loadTemplate($event.target.value); $event.target.value = ''"
                            >
                                <flux:select.option value="">Templates</flux:select.option>
                                <template x-for="t in templates" :key="t.label">
                                    <flux:select.option ::value="t.value" x-text="t.label"></flux:select.option>
                                </template>
                            </flux:select>
                            <flux:button x-on:click="clear()" x-bind:disabled="!code" icon="trash" size="sm" variant="filled">
                                Clear
                            </flux:button>
                        </div>
                    </div>

                    <flux:textarea
                        name="code"
                        x-model="code"
                        placeholder="flowchart TD&#10;    A --> B"
                        rows="18"
                        class="font-mono"
                        x-bind:class="isFullscreen && 'min-h-[72vh]'"
                        spellcheck="false"
                    />

                    <div
                        x-show="error"
                        x-cloak
                        class="mt-6 rounded-md border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-700 dark:text-red-300"
                    >
                        <div class="mb-1 font-semibold">Diagram error</div>
                        <pre class="overflow-x-auto font-mono text-xs whitespace-pre-wrap" x-text="error"></pre>
                    </div>
                </div>

                {{-- Preview --}}
                <div
                    class="flex flex-col rounded-lg border border-black/10 p-8 dark:border-white/10"
                    x-bind:class="isFullscreen && 'h-full min-w-0 flex-1 bg-white dark:bg-zinc-950'"
                >
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-2 border-b border-black/10 pb-4 dark:border-white/10">
                        <flux:heading size="xl">Preview</flux:heading>
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:select size="sm" x-model="theme" aria-label="Theme">
                                <flux:select.option value="auto">Auto (match site)</flux:select.option>
                                <flux:select.option value="default">Default</flux:select.option>
                                <flux:select.option value="neutral">Neutral</flux:select.option>
                                <flux:select.option value="dark">Dark</flux:select.option>
                                <flux:select.option value="forest">Forest</flux:select.option>
                                <flux:select.option value="base">Base</flux:select.option>
                            </flux:select>
                            <flux:button
                                x-show="isFullscreen && showEditor"
                                x-cloak
                                x-on:click="toggleEditor()"
                                icon="code-bracket"
                                size="sm"
                                variant="filled"
                                aria-label="Hide editor"
                            />
                            <flux:button
                                x-show="isFullscreen && !showEditor"
                                x-cloak
                                x-on:click="toggleEditor()"
                                icon="code-bracket"
                                size="sm"
                                variant="ghost"
                                aria-label="Show editor"
                            />
                            <flux:button x-on:click="zoomOut()" x-bind:disabled="!hasDiagram" icon="minus" size="sm" variant="ghost" aria-label="Zoom out" />
                            <flux:button x-on:click="resetView()" x-bind:disabled="!hasDiagram" icon="viewfinder-circle" size="sm" variant="ghost" aria-label="Fit to view" />
                            <flux:button x-on:click="zoomIn()" x-bind:disabled="!hasDiagram" icon="plus" size="sm" variant="ghost" aria-label="Zoom in" />
                            <flux:button
                                x-show="!isFullscreen"
                                x-on:click="toggleFullscreen()"
                                icon="arrows-pointing-out"
                                size="sm"
                                variant="ghost"
                                aria-label="Fullscreen"
                            />
                            <flux:button
                                x-show="isFullscreen"
                                x-cloak
                                x-on:click="toggleFullscreen()"
                                icon="arrows-pointing-in"
                                size="sm"
                                variant="ghost"
                                aria-label="Exit fullscreen"
                            />
                        </div>
                    </div>

                    <div
                        x-ref="preview"
                        class="relative flex items-center justify-center overflow-hidden rounded-md border border-black/5 dark:border-white/5"
                        x-bind:class="{
                            'bg-zinc-900': resolvedTheme === 'dark',
                            'bg-white': resolvedTheme !== 'dark',
                            'min-h-0 flex-1': isFullscreen,
                            'h-[460px]': !isFullscreen,
                        }"
                    ></div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <x-copy-button
                            value="currentSvg()"
                            flash="'mermaid-svg'"
                            label="Copy SVG"
                            size="sm"
                            x-bind:disabled="!hasDiagram"
                        />
                        <flux:button x-on:click="downloadSvg()" x-bind:disabled="!hasDiagram" icon="arrow-down-tray" size="sm" variant="ghost">
                            SVG
                        </flux:button>
                        <flux:button x-on:click="downloadPng()" x-bind:disabled="!hasDiagram" icon="arrow-down-tray" size="sm" variant="ghost">
                            PNG
                        </flux:button>
                        <flux:button x-on:click="openInMermaidLive()" x-bind:disabled="!code.trim()" icon="arrow-top-right-on-square" size="sm" variant="ghost">
                            Open in mermaid.live
                        </flux:button>
                        <flux:text size="xs" class="ml-auto opacity-60">Drag to pan, scroll to zoom.</flux:text>
                    </div>
                </div>
            </div>

            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="The URL below carries your diagram source. Anyone who opens it sees the same diagram."
                tooLongMessage="This diagram is too large to fit in the URL. Export the SVG to share it instead."
            />
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
