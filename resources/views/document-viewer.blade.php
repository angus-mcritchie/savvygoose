<x-layouts.document>
    <div x-data="documentViewer">
        {{-- Reading mode. The document supplies its own title via its first
             heading, so nothing is repeated above it. --}}
        <div x-show="hasDocument" x-cloak>
            <div class="mb-8 flex flex-wrap justify-end gap-2 print:hidden">
                <x-copy-button value="doc" flash="'doc-markdown'" label="Copy Markdown" size="sm" variant="outline" />
                <flux:button
                    x-on:click="$download(doc, filename, 'text/markdown')"
                    icon="arrow-down-tray"
                    size="sm"
                >
                    .md
                </flux:button>
                <flux:tooltip content="Opens your browser's print dialog, where you can also save the document as a PDF.">
                    <flux:button x-on:click="window.print()" icon="printer" size="sm">Print</flux:button>
                </flux:tooltip>
                {{-- The static href is what makes Flux render an <a> at all; the
                     binding then upgrades it to carry this document across. --}}
                <flux:button
                    href="{{ route('markdown-converter') }}"
                    x-bind:href="editUrl"
                    icon="pencil-square"
                    size="sm"
                    variant="filled"
                >
                    Edit a copy
                </flux:button>
            </div>

            <article class="prose max-w-none dark:prose-invert" x-html="html"></article>

            <p class="mt-10 text-xs text-zinc-500 print:hidden dark:text-zinc-400">
                This document is carried inside the link you followed. Nothing was uploaded, and editing a copy
                leaves the original link exactly as it was sent.
            </p>
        </div>

        {{-- Empty state: the page arrived at directly, with no document in the URL. --}}
        <div x-show="!hasDocument" x-cloak>
            <div class="mb-8 grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.document-text class="size-16 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Document Viewer</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Turn Markdown into a read-only page you can share.
                    </flux:heading>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-6 dark:border-white/10">
                <flux:textarea
                    name="doc"
                    x-model="doc"
                    label="Markdown"
                    placeholder="# Project notes&#10;&#10;Paste **Markdown** here and the page becomes the document."
                    rows="12"
                    class="font-mono"
                />
                <flux:subheading class="mt-4">
                    As soon as there is something here, the address bar holds the whole document. Copy that URL and
                    whoever opens it sees the rendered page, with no editor and nothing to sign into.
                </flux:subheading>
            </div>

            <x-tool-content />
        </div>
    </div>
</x-layouts.document>
