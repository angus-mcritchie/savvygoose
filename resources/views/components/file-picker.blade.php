@props([
    'accept' => '*',
    'binding' => 'file',
    'onChange',
    'onClear',
    'reference' => 'fileInput',
    'helper' => null,
    'error' => null,
])

<div class="grid gap-4">
    <div class="grid gap-4 sm:grid-cols-[auto_1fr] sm:items-center">
        <input
            type="file"
            accept="{{ $accept }}"
            x-ref="{{ $reference }}"
            x-on:change="{{ $onChange }}($event)"
            class="hidden"
        />
        <flux:button
            type="button"
            x-on:click="$refs.{{ $reference }}.click()"
            icon="document-arrow-up"
        >
            {{-- Server-rendered label: an empty span would size the button wrong until Alpine boots. --}}
            <span x-text="{{ $binding }} ? 'Replace file' : 'Choose a file'">Choose a file</span>
        </flux:button>
        {{-- x-show rather than x-if so the row is in the DOM from the first paint; the
             bindings therefore run with no file selected and have to tolerate null. --}}
        <div
            class="flex items-center justify-between gap-4 rounded-md bg-zinc-100 px-4 py-2 text-sm dark:bg-zinc-700"
            x-show="{{ $binding }}"
            x-cloak
        >
            <div class="truncate">
                <span class="font-medium" x-text="{{ $binding }}?.name ?? ''"></span>
                <span class="opacity-60" x-text="{{ $binding }} ? ' · ' + $formatBytes({{ $binding }}.size) : ''"></span>
            </div>
            <flux:button
                type="button"
                x-on:click="{{ $onClear }}()"
                icon="x-mark"
                size="xs"
                variant="ghost"
                aria-label="Remove selected file"
            />
        </div>
        @if ($helper)
            <p class="text-sm opacity-60" x-show="!{{ $binding }}">{{ $helper }}</p>
        @endif
    </div>

    @if ($error)
        <p
            x-show="{{ $error }}"
            x-cloak
            x-text="{{ $error }}"
            class="text-sm text-red-600 dark:text-red-400"
            role="alert"
            aria-live="assertive"
        ></p>
    @endif
</div>
