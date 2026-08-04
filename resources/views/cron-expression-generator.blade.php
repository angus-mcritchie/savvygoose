<x-layouts.app>
    <div class="mx-auto max-w-[900px]" x-data="cronExpression">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.clock class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Cron Expression Generator</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Write a cron schedule, read it in plain English, and see the next runs.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                @php
                    // Rendered server-side rather than with x-for so the preset row is laid out
                    // before Alpine boots. Mirrors PRESETS in resources/js/data/cronExpression.js.
                    $presets = [
                        'Every minute' => '* * * * *',
                        'Every 5 minutes' => '*/5 * * * *',
                        'Every hour' => '0 * * * *',
                        'Every day at midnight' => '0 0 * * *',
                        'Every day at 9am' => '0 9 * * *',
                        'Weekdays at 9am' => '0 9 * * 1-5',
                        'Every Monday' => '0 0 * * 1',
                        'First of the month' => '0 0 1 * *',
                    ];
                @endphp
                <div class="mb-4 flex flex-wrap gap-2">
                    @foreach ($presets as $label => $expr)
                        <button
                            type="button"
                            x-on:click="setPreset(@js($expr))"
                            class="min-h-9 rounded-md border border-black/10 px-3 py-2 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:border-white/10 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-zinc-100"
                        >{{ $label }}</button>
                    @endforeach
                </div>

                <flux:input
                    x-model="expr"
                    label="Cron expression"
                    class="!font-mono !text-lg"
                    autocomplete="off"
                    spellcheck="false"
                />

                <div class="mt-2 grid grid-cols-5 gap-1 text-center font-mono text-[10px] text-zinc-500 sm:gap-2 sm:text-[11px] dark:text-zinc-400">
                    <span>minute<br>0–59</span>
                    <span>hour<br>0–23</span>
                    <span>day<br>1–31</span>
                    <span>month<br>1–12</span>
                    <span>weekday<br>0–6</span>
                </div>

                <div
                    x-show="error"
                    x-cloak
                    class="mt-4 rounded-md border border-red-500/40 bg-red-500/10 px-3 py-2 text-sm text-red-700 dark:text-red-300"
                    role="alert"
                    aria-live="assertive"
                    x-text="error"
                ></div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2" x-show="!error">
                <div class="rounded-lg border border-black/10 p-6 dark:border-white/10" aria-live="polite">
                    <flux:heading size="sm" class="mb-3 text-zinc-500 dark:text-zinc-400">In plain English</flux:heading>
                    {{-- min-h holds two text-lg lines so the description landing does not push the card. --}}
                    <p class="min-h-14 text-lg" x-text="description">&nbsp;</p>
                </div>
                <div class="rounded-lg border border-black/10 p-6 dark:border-white/10">
                    <flux:heading size="sm" class="mb-3 text-zinc-500 dark:text-zinc-400">Next 5 runs (your local time)</flux:heading>
                    {{-- Five fixed rows instead of x-for: the run times are local to the visitor, but
                         the list is always at most five lines, so the box can hold its height up front. --}}
                    <ul class="grid gap-1 font-mono text-sm tabular-nums">
                        @for ($i = 0; $i < 5; $i++)
                            <li x-text="runs[{{ $i }}] ?? ''">&nbsp;</li>
                        @endfor
                    </ul>
                    <p x-show="runs.length === 0" x-cloak class="text-sm text-zinc-500 dark:text-zinc-400">No runs in the next year.</p>
                </div>
            </div>

            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="The URL below carries your cron expression."
                tooLongMessage="Expression is too long to include in the URL."
            />
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
