@props([
    'value',
    'flash' => null,
    'label' => 'Copy',
    'copiedLabel' => 'Copied!',
    'icon' => 'document-duplicate',
    'iconCopied' => 'check',
    'variant' => 'ghost',
    'size' => 'sm',
])

@php
    $flashExpr = $flash ?: "'_copy_" . uniqid() . "'";
@endphp

<flux:button
    type="button"
    :variant="$variant"
    :size="$size"
    x-bind:icon="$store.copy.is({{ $flashExpr }}) ? '{{ $iconCopied }}' : '{{ $icon }}'"
    x-on:click.stop="$copy({{ $value }}, {{ $flashExpr }})"
    {{ $attributes }}
>
    <span x-show="!$store.copy.is({{ $flashExpr }})">{{ $label }}</span>
    <span x-show="$store.copy.is({{ $flashExpr }})" x-cloak>{{ $copiedLabel }}</span>
</flux:button>
