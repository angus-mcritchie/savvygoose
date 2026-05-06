@props([
    'icon',
    'class' => 'size-20',
])

@if ($icon['type'] === 'flux')
    <flux:icon :name="$icon['name']" {{ $attributes->class([$class.' text-zinc-700 dark:text-zinc-200']) }} />
@else
    <img {{ $attributes->class([$class]) }} src="{{ asset($icon['src']) }}" width="128" height="128" alt="" />
@endif
