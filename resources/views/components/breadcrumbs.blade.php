@props(['items' => null])

@php
    $items = $items ?? (\App\Support\Seo::forCurrent()['breadcrumbs'] ?? []);
@endphp

@if (count($items) > 1)
    <nav aria-label="Breadcrumb" class="mx-auto mb-6 max-w-[1200px] text-sm">
        <ol class="flex flex-wrap items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
            @foreach ($items as $i => $crumb)
                <li class="flex items-center gap-1.5">
                    @if ($i < count($items) - 1)
                        <a
                            href="{{ $crumb['url'] }}"
                            wire:navigate
                            class="transition hover:text-zinc-900 hover:underline dark:hover:text-zinc-100"
                        >{{ $crumb['name'] }}</a>
                        <flux:icon.chevron-right class="size-3.5 opacity-50" />
                    @else
                        <span class="text-zinc-700 dark:text-zinc-300" aria-current="page">{{ $crumb['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
