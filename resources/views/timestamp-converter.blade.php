<x-layouts.app>
    @php
        $rowClass = 'grid grid-cols-[10rem_1fr_auto] items-center gap-3 sm:grid-cols-[12rem_1fr_auto]';
        $valClass = 'truncate font-mono text-sm sm:text-base text-zinc-800 dark:text-zinc-100 tabular-nums';
        $labelClass = 'text-sm text-zinc-500 dark:text-zinc-400';
        $timezones = \DateTimeZone::listIdentifiers();
    @endphp

    <div class="mx-auto max-w-[1100px]" x-data="timestampConverter">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.clock class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Timestamp Converter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Unix epoch ↔ ISO 8601 ↔ human time, with a timezone picker.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="grid gap-4 rounded-lg border border-black/10 p-6 dark:border-white/10 sm:grid-cols-[1fr_auto] sm:items-end">
                <flux:select
                    variant="combobox"
                    x-model="tz"
                    label="Timezone"
                    placeholder="Search timezones…"
                >
                    @foreach ($timezones as $zone)
                        <flux:select.option value="{{ $zone }}">{{ $zone }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div class="text-right text-sm">
                    <div class="{{ $labelClass }}">Now</div>
                    <div class="font-mono text-zinc-800 dark:text-zinc-100 tabular-nums" x-text="nowFormatted()"></div>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
                <flux:heading class="mb-2" size="xl">1. Input</flux:heading>
                <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">
                    Paste or type any timestamp: Unix seconds, milliseconds, or ISO 8601.
                </flux:subheading>

                <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                    <flux:input
                        x-model="rawInput"
                        x-on:input="refreshHint()"
                        x-on:change="parseRaw()"
                        x-on:keydown.enter.prevent="parseRaw()"
                        placeholder="1762534200 or 2026-05-07T14:30:00Z"
                        label="Timestamp"
                    />
                    <div class="flex gap-2">
                        <flux:button type="button" x-on:click="parseRaw()" variant="primary">Convert</flux:button>
                        <flux:button type="button" x-on:click="setNow()">Now</flux:button>
                    </div>
                </div>

                <p
                    class="mt-3 text-sm text-zinc-500 dark:text-zinc-400"
                    x-show="parseHint && !parseError"
                    x-cloak
                    x-text="parseHint"
                ></p>
                <p
                    class="mt-3 text-sm text-red-600 dark:text-red-400"
                    x-show="parseError"
                    x-cloak
                    x-text="parseError"
                ></p>

                <flux:separator class="my-6" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:date-picker
                        type="input"
                        x-model="selectedDate"
                        label="Date (in selected timezone)"
                    />
                    <flux:time-picker
                        type="input"
                        x-model="selectedTime"
                        label="Time"
                    />
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
                <div class="flex items-start justify-between gap-2">
                    <flux:heading class="mb-2" size="xl">2. Conversions</flux:heading>
                    <flux:dropdown position="bottom" align="end">
                        <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="About these formats" />
                        <flux:popover class="max-w-sm">
                            <flux:heading size="sm">The formats</flux:heading>
                            <ul class="mt-2 space-y-2 text-sm">
                                <li><strong>Unix seconds</strong>: integer count of seconds since 1970-01-01 UTC. Compact &amp; timezone-free.</li>
                                <li><strong>Unix milliseconds</strong>: same epoch, ×1000. JavaScript's <code class="font-mono">Date.now()</code>.</li>
                                <li><strong>ISO 8601 (UTC)</strong>: human-readable string ending in <code class="font-mono">Z</code>.</li>
                                <li><strong>ISO 8601 (zoned)</strong>: same instant with a numeric offset like <code class="font-mono">+10:00</code>.</li>
                            </ul>
                            <flux:separator class="my-3" />
                            <p class="text-sm">All four describe the <em>same instant</em>. Only the wall-clock display changes.</p>
                        </flux:popover>
                    </flux:dropdown>
                </div>
                <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">
                    All times below reflect the selected timezone.
                </flux:subheading>

                <div class="grid gap-3">
                    <div class="{{ $rowClass }}">
                        <span class="{{ $labelClass }}">Unix seconds</span>
                        <span class="{{ $valClass }}" x-text="unixSeconds()"></span>
                        <flux:button type="button" size="xs" variant="ghost" icon="document-duplicate" x-on:click="copy('s', unixSeconds())">
                            <span x-text="copied === 's' ? 'Copied!' : 'Copy'">Copy</span>
                        </flux:button>
                    </div>
                    <div class="{{ $rowClass }}">
                        <span class="{{ $labelClass }}">Unix milliseconds</span>
                        <span class="{{ $valClass }}" x-text="unixMilliseconds()"></span>
                        <flux:button type="button" size="xs" variant="ghost" icon="document-duplicate" x-on:click="copy('ms', unixMilliseconds())">
                            <span x-text="copied === 'ms' ? 'Copied!' : 'Copy'">Copy</span>
                        </flux:button>
                    </div>
                    <div class="{{ $rowClass }}">
                        <span class="{{ $labelClass }}">ISO 8601 (UTC)</span>
                        <span class="{{ $valClass }}" x-text="isoUtc()"></span>
                        <flux:button type="button" size="xs" variant="ghost" icon="document-duplicate" x-on:click="copy('utc', isoUtc())">
                            <span x-text="copied === 'utc' ? 'Copied!' : 'Copy'">Copy</span>
                        </flux:button>
                    </div>
                    <div class="{{ $rowClass }}">
                        <span class="{{ $labelClass }}">ISO 8601 (zoned)</span>
                        <span class="{{ $valClass }}" x-text="isoZoned()"></span>
                        <flux:button type="button" size="xs" variant="ghost" icon="document-duplicate" x-on:click="copy('zoned', isoZoned())">
                            <span x-text="copied === 'zoned' ? 'Copied!' : 'Copy'">Copy</span>
                        </flux:button>
                    </div>
                    <div class="{{ $rowClass }}">
                        <span class="{{ $labelClass }}">Human</span>
                        <span class="{{ $valClass }} !font-sans" x-text="humanLong()"></span>
                        <flux:button type="button" size="xs" variant="ghost" icon="document-duplicate" x-on:click="copy('human', humanLong())">
                            <span x-text="copied === 'human' ? 'Copied!' : 'Copy'">Copy</span>
                        </flux:button>
                    </div>
                    <div class="{{ $rowClass }}">
                        <span class="{{ $labelClass }}">Relative to now</span>
                        <span class="{{ $valClass }} !font-sans" x-text="relative()"></span>
                        <span></span>
                    </div>
                </div>
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
