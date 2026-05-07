<x-layouts.app>
    @php
        $countries = config('countries');
    @endphp

    <div class="mx-auto max-w-[1100px]" x-data="timeBetweenDates({{ Js::from(['supported' => array_keys($countries)]) }})">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.calendar-days class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Time Between Dates</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Count calendar days, business days, weekends &amp; public holidays between two dates.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
                <flux:heading class="mb-2" size="xl">1. Dates</flux:heading>
                <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">
                    Pick a start and end date. Order doesn't matter — we'll always count forward.
                </flux:subheading>

                <div class="grid gap-4 sm:grid-cols-[1fr_auto_1fr] sm:items-end">
                    <flux:date-picker
                        type="input"
                        x-model="start"
                        label="Start date"
                    />
                    <div class="flex justify-center pb-1">
                        <flux:button
                            type="button"
                            size="sm"
                            variant="ghost"
                            icon="arrows-right-left"
                            x-on:click="swap"
                            aria-label="Swap dates"
                        />
                    </div>
                    <flux:date-picker
                        type="input"
                        x-model="end"
                        label="End date"
                    />
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <flux:button type="button" size="sm" variant="subtle" x-on:click="setPreset('today')">Today → today</flux:button>
                    <flux:button type="button" size="sm" variant="subtle" x-on:click="setPreset('next-30')">Next 30 days</flux:button>
                    <flux:button type="button" size="sm" variant="subtle" x-on:click="setPreset('next-90')">Next 90 days</flux:button>
                    <flux:button type="button" size="sm" variant="subtle" x-on:click="setPreset('this-year')">This year</flux:button>
                    <flux:button type="button" size="sm" variant="subtle" x-on:click="setPreset('next-year')">Next year</flux:button>
                </div>

                <flux:separator class="my-6" />

                <div class="grid gap-4 sm:grid-cols-2 sm:items-end">
                    <flux:select
                        variant="combobox"
                        x-model="country"
                        label="Public holidays calendar"
                        placeholder="Search countries…"
                    >
                        <flux:select.option value="">No holidays calendar</flux:select.option>
                        @foreach ($countries as $code => $label)
                            <flux:select.option value="{{ $code }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:checkbox
                        x-model="inclusive"
                        label="Include both start and end dates"
                        description="When off, we count nights instead of days."
                    />
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
                <div class="flex items-start justify-between gap-2">
                    <flux:heading class="mb-2" size="xl">2. Result</flux:heading>
                    <flux:dropdown position="bottom" align="end">
                        <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="About these counts" />
                        <flux:popover class="max-w-sm">
                            <flux:heading size="sm">How we count</flux:heading>
                            <ul class="mt-2 space-y-2 text-sm">
                                <li><strong>Calendar days</strong>: every day in the range.</li>
                                <li><strong>Weekdays</strong>: Monday through Friday.</li>
                                <li><strong>Weekends</strong>: Saturday and Sunday.</li>
                                <li><strong>Public holidays</strong>: from the selected country's calendar. Holidays that fall on a weekend are still counted as weekend days.</li>
                                <li><strong>Business days</strong>: weekdays minus public holidays that fall on a weekday.</li>
                            </ul>
                        </flux:popover>
                    </flux:dropdown>
                </div>

                <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" x-text="rangeSummary()"></flux:subheading>

                <div x-show="errorMessage" x-cloak class="mb-4 rounded-md border border-red-500/40 bg-red-500/10 px-3 py-2 text-sm text-red-700 dark:text-red-300" x-text="errorMessage"></div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" x-show="!errorMessage" x-cloak>
                    <div class="rounded-md border border-black/10 px-4 py-3 dark:border-white/10">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Calendar days</div>
                        <div class="mt-1 font-mono text-2xl tabular-nums" x-text="formatNumber(stats.totalDays)"></div>
                    </div>
                    <div class="rounded-md border border-black/10 px-4 py-3 dark:border-white/10">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Weekdays</div>
                        <div class="mt-1 font-mono text-2xl tabular-nums" x-text="formatNumber(stats.weekdays)"></div>
                    </div>
                    <div class="rounded-md border border-black/10 px-4 py-3 dark:border-white/10">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Weekend days</div>
                        <div class="mt-1 font-mono text-2xl tabular-nums" x-text="formatNumber(stats.weekendDays)"></div>
                    </div>
                    <div class="rounded-md border border-black/10 px-4 py-3 dark:border-white/10">
                        <div class="flex items-center gap-1 text-sm text-zinc-500 dark:text-zinc-400">
                            <span>Public holidays</span>
                            <span x-show="holidaysLoading" x-cloak class="text-xs opacity-60">(loading…)</span>
                        </div>
                        <div class="mt-1 font-mono text-2xl tabular-nums" x-text="country ? formatNumber(stats.holidays) : '—'"></div>
                    </div>
                    <div class="rounded-md border border-black/10 px-4 py-3 dark:border-white/10 sm:col-span-2 lg:col-span-2">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Business days <span class="text-xs opacity-60">(weekdays minus holidays)</span></div>
                        <div class="mt-1 font-mono text-2xl tabular-nums" x-text="formatNumber(stats.businessDays)"></div>
                    </div>
                </div>

                <flux:separator class="my-6" />

                <flux:heading size="sm" class="mb-3">Duration breakdown</flux:heading>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                    <div><span class="text-zinc-500 dark:text-zinc-400">Years &amp; days:</span> <span class="font-mono tabular-nums" x-text="stats.calendarBreakdown"></span></div>
                    <div><span class="text-zinc-500 dark:text-zinc-400">Weeks:</span> <span class="font-mono tabular-nums" x-text="formatDecimal(stats.totalDays / 7)"></span></div>
                    <div><span class="text-zinc-500 dark:text-zinc-400">Hours:</span> <span class="font-mono tabular-nums" x-text="formatNumber(stats.totalDays * 24)"></span></div>
                    <div><span class="text-zinc-500 dark:text-zinc-400">Minutes:</span> <span class="font-mono tabular-nums" x-text="formatNumber(stats.totalDays * 24 * 60)"></span></div>
                </div>
            </div>

            <div
                class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10"
                x-show="country && (holidaysInRange.length || holidaysError)"
                x-cloak
            >
                <flux:heading class="mb-2" size="xl">Holidays in range</flux:heading>
                <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">
                    <span x-text="holidaysSubheading()"></span>
                </flux:subheading>

                <div x-show="holidaysError" x-cloak class="rounded-md border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm text-amber-700 dark:text-amber-300" x-text="holidaysError"></div>

                <ul x-show="!holidaysError" x-cloak class="grid gap-2 sm:grid-cols-2">
                    <template x-for="h in holidaysInRange" :key="h.date + h.name">
                        <li class="flex items-baseline justify-between gap-3 rounded-md border border-black/10 px-3 py-2 dark:border-white/10">
                            <span class="font-medium" x-text="h.name"></span>
                            <span class="font-mono text-sm tabular-nums opacity-70" x-text="formatHolidayDate(h.date)"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:input
                    type="url"
                    x-model="url"
                    readonly
                    copyable
                    label="Share URL"
                />
            </div>
        </div>
    </div>
</x-layouts.app>
