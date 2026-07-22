@props(['slug' => null])

@php
    $tools = collect(config('tools.tools'));
    $resolvedSlug = $slug ?? request()->route()?->getName();
    $tool = $tools->firstWhere('slug', $resolvedSlug);
@endphp

@if ($tool)
    @php
        $categoryKey = $tool['category'];
        $categoryLabel = config('tools.categories.'.$categoryKey);
        $bySlug = $tools->keyBy('slug');

        // Hand-picked cross-cluster links first, then same-category siblings.
        $picks = collect($tool['related'] ?? [])
            ->map(fn ($s) => $bySlug->get($s))
            ->filter();

        $siblings = $tools
            ->where('category', $categoryKey)
            ->where('slug', '!=', $resolvedSlug);

        $related = $picks
            ->concat($siblings)
            ->unique('slug')
            ->reject(fn ($t) => $t['slug'] === $resolvedSlug)
            ->take(6)
            ->values();
    @endphp

    @if ($related->isNotEmpty())
        <nav aria-label="Related tools" class="mx-auto mt-16 max-w-3xl border-t border-black/5 pt-10 dark:border-white/10">
            <flux:heading class="!mb-6 !text-2xl !font-semibold tracking-tight" level="2">Related tools</flux:heading>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($related as $rel)
                    <li>
                        <a
                            href="{{ route($rel['slug']) }}"
                            wire:navigate
                            class="flex items-start gap-3 rounded-lg border border-black/10 p-4 transition hover:border-black/20 dark:border-white/10 dark:hover:border-white/20"
                        >
                            <x-tool-icon :icon="$rel['icon']" class="size-6" />
                            <span>
                                <span class="block font-medium text-zinc-900 dark:text-zinc-100">{{ $rel['name'] }}</span>
                                <span class="block text-sm text-zinc-600 dark:text-zinc-400">{{ $rel['tagline'] }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <a
                href="{{ route('category.'.$categoryKey) }}"
                wire:navigate
                class="mt-6 inline-flex items-center gap-1 text-sm font-medium text-zinc-600 underline-offset-4 hover:underline dark:text-zinc-400"
            >
                See all {{ $categoryLabel }}
                <flux:icon.arrow-right class="size-4" />
            </a>
        </nav>
    @endif
@endif
