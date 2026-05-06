@props(['label', 'value', 'placeholder' => '0'])

<div {{ $attributes->class('rounded-lg border border-black/10 p-6 text-center dark:border-white/10') }}>
    <flux:subheading class="mb-2" size="lg">{{ $label }}</flux:subheading>
    <flux:heading class="!text-5xl" x-text="{{ $value }}">{{ $placeholder }}</flux:heading>
</div>
