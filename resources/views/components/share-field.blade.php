@props([
    'subheading' => 'Share this configuration with anyone — the URL below reproduces it exactly.',
    'tooLongMessage' => 'Some inputs are too long to fit in the URL.',
    'heading' => 'Share',
])

<div {{ $attributes }}>
    @if ($heading)
        <flux:heading class="mb-2" size="xl">{{ $heading }}</flux:heading>
    @endif
    @if ($subheading)
        <flux:subheading class="mb-4">
            {{ $subheading }}
        </flux:subheading>
    @endif
    <p x-show="urlTooLong" x-cloak class="mb-4 text-sm text-amber-600 dark:text-amber-400">
        {{ $tooLongMessage }}
    </p>
    <flux:input type="url" x-model="url" readonly copyable label="Share URL" />
    <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
        Anyone with this link can read the values it contains. Do not use it for sensitive data.
    </p>
    {{ $slot }}
</div>
