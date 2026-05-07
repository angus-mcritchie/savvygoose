@props(['slug' => null])

@php
    $resolvedSlug = $slug ?? request()->route()?->getName();
    $tool = collect(config('tools.tools'))->firstWhere('slug', $resolvedSlug);
@endphp

@if ($tool)
    <section class="mx-auto mt-12 grid max-w-3xl gap-12 text-zinc-700 dark:text-zinc-300">
        @if (! empty($tool['howto']))
            <div>
                <flux:heading class="!mb-4 !text-2xl !font-semibold tracking-tight" level="2">
                    How to use the {{ $tool['name'] }}
                </flux:heading>
                <ol class="ml-5 list-decimal space-y-2 leading-relaxed">
                    @foreach ($tool['howto'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </div>
        @endif

        @if (! empty($tool['faqs']))
            <div>
                <flux:heading class="!mb-4 !text-2xl !font-semibold tracking-tight" level="2">
                    Frequently asked questions
                </flux:heading>
                <dl class="grid gap-6">
                    @foreach ($tool['faqs'] as $faq)
                        <div class="border-b border-black/5 pb-6 last:border-b-0 last:pb-0 dark:border-white/10">
                            <dt class="mb-2 font-semibold text-zinc-900 dark:text-zinc-100">{{ $faq['q'] }}</dt>
                            <dd class="leading-relaxed">{{ $faq['a'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </section>
@endif
