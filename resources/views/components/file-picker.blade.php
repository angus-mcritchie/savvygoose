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
            <span x-text="{{ $binding }} ? 'Replace file' : 'Choose a file'"></span>
        </flux:button>
        <template x-if="{{ $binding }}">
            <div class="flex items-center justify-between gap-4 rounded-md bg-zinc-100 px-4 py-2 text-sm dark:bg-zinc-700">
                <div class="truncate">
                    <span class="font-medium" x-text="{{ $binding }}.name"></span>
                    <span class="opacity-60" x-text="' — ' + $formatBytes({{ $binding }}.size)"></span>
                </div>
                <flux:button x-on:click="{{ $onClear }}()" icon="x-mark" size="xs" variant="ghost" />
            </div>
        </template>
        @if ($helper)
            <template x-if="!{{ $binding }}">
                <p class="text-sm opacity-60">{{ $helper }}</p>
            </template>
        @endif
    </div>

    @if ($error)
        <p
            x-show="{{ $error }}"
            x-cloak
            x-text="{{ $error }}"
            class="text-sm text-red-600 dark:text-red-400"
        ></p>
    @endif
</div>
