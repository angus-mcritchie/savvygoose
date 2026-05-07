<x-layouts.app>

    @php
        $rowClass = 'grid grid-cols-[10rem_1fr] gap-3 py-2 border-b border-black/5 dark:border-white/5 last:border-0';
        $labelClass = 'text-sm text-zinc-500 dark:text-zinc-400';
        $valClass = 'font-mono text-sm text-zinc-800 dark:text-zinc-100 break-all';
    @endphp

    <div class="mx-auto max-w-[1100px]" x-data="browserInfo">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.computer-desktop class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Browser Info</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Viewport, screen size, device pixel ratio, browser & OS detection.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-black/10 p-6 dark:border-white/10 sm:p-8">
                <flux:heading class="mb-4 border-b border-black/10 pb-3 dark:border-white/10" size="lg">Display</flux:heading>
                <dl>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Viewport</dt>
                        <dd class="{{ $valClass }} tabular-nums">
                            <span x-text="viewportW"></span> × <span x-text="viewportH"></span> px
                        </dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Screen</dt>
                        <dd class="{{ $valClass }} tabular-nums">
                            <span x-text="screenW"></span> × <span x-text="screenH"></span> px
                        </dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Available screen</dt>
                        <dd class="{{ $valClass }} tabular-nums">
                            <span x-text="availScreenW"></span> × <span x-text="availScreenH"></span> px
                        </dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }} flex items-center gap-1">
                            <span>DPR</span>
                            <flux:dropdown position="bottom" align="start">
                                <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="What is DPR?" />
                                <flux:popover class="max-w-sm">
                                    <flux:heading size="sm">Device pixel ratio</flux:heading>
                                    <p class="mt-2 text-sm">The number of physical pixels per CSS pixel. <code>1</code> on a non-Retina monitor, <code>2</code> on most Retina displays, often <code>2.5</code>–<code>3</code> on phones. Browser zoom changes this too.</p>
                                </flux:popover>
                            </flux:dropdown>
                        </dt>
                        <dd class="{{ $valClass }} tabular-nums" x-text="dpr"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Color depth</dt>
                        <dd class="{{ $valClass }} tabular-nums"><span x-text="colorDepth"></span>-bit</dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Orientation</dt>
                        <dd class="{{ $valClass }}" x-text="orientation"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Color scheme</dt>
                        <dd class="{{ $valClass }}" x-text="colorScheme"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Reduced motion</dt>
                        <dd class="{{ $valClass }}" x-text="reducedMotion ? 'Reduce' : 'No preference'"></dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-black/10 p-6 dark:border-white/10 sm:p-8">
                <flux:heading class="mb-4 border-b border-black/10 pb-3 dark:border-white/10" size="lg">Browser</flux:heading>
                <dl>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Browser</dt>
                        <dd class="{{ $valClass }}">
                            <span x-text="parsed.browser"></span>
                            <span class="opacity-60" x-text="parsed.browserVersion ? ' ' + parsed.browserVersion : ''"></span>
                        </dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Engine</dt>
                        <dd class="{{ $valClass }}" x-text="parsed.engine"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Languages</dt>
                        <dd class="{{ $valClass }}" x-text="languages"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Cookies</dt>
                        <dd class="{{ $valClass }}" x-text="cookiesEnabled ? 'Enabled' : 'Disabled'"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Online</dt>
                        <dd class="{{ $valClass }}" x-text="online ? 'Yes' : 'No'"></dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-black/10 p-6 dark:border-white/10 sm:p-8">
                <flux:heading class="mb-4 border-b border-black/10 pb-3 dark:border-white/10" size="lg">System</flux:heading>
                <dl>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">OS</dt>
                        <dd class="{{ $valClass }}">
                            <span x-text="parsed.os"></span>
                            <span class="opacity-60" x-text="parsed.osVersion ? ' ' + parsed.osVersion : ''"></span>
                        </dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Touch points</dt>
                        <dd class="{{ $valClass }} tabular-nums" x-text="touchPoints"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Logical cores</dt>
                        <dd class="{{ $valClass }} tabular-nums" x-text="cores ?? 'Unavailable'"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }} flex items-center gap-1">
                            <span>Device memory</span>
                            <flux:dropdown position="bottom" align="start">
                                <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="About device memory" />
                                <flux:popover class="max-w-sm">
                                    <p class="text-sm"><code>navigator.deviceMemory</code> is buckets of approximate RAM (0.25 / 0.5 / 1 / 2 / 4 / 8 GB) — capped at 8 for fingerprinting reasons. Firefox & Safari don't expose it.</p>
                                </flux:popover>
                            </flux:dropdown>
                        </dt>
                        <dd class="{{ $valClass }}" x-text="deviceMemory ?? 'Unavailable'"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }} flex items-center gap-1">
                            <span>Connection</span>
                            <flux:dropdown position="bottom" align="start">
                                <flux:button icon="information-circle" variant="ghost" size="xs" aria-label="About connection info" />
                                <flux:popover class="max-w-sm">
                                    <p class="text-sm">The Network Information API. Only Chromium browsers expose it; Firefox & Safari report nothing.</p>
                                </flux:popover>
                            </flux:dropdown>
                        </dt>
                        <dd class="{{ $valClass }}" x-text="connection ?? 'Unavailable'"></dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-black/10 p-6 dark:border-white/10 sm:p-8">
                <flux:heading class="mb-4 border-b border-black/10 pb-3 dark:border-white/10" size="lg">Locale</flux:heading>
                <dl>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">Timezone</dt>
                        <dd class="{{ $valClass }}" x-text="timezone || 'Unavailable'"></dd>
                    </div>
                    <div class="{{ $rowClass }}">
                        <dt class="{{ $labelClass }}">UTC offset</dt>
                        <dd class="{{ $valClass }} tabular-nums" x-text="tzOffset"></dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 rounded-lg border border-black/10 p-6 dark:border-white/10 sm:p-8">
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="lg">User agent</flux:heading>
                <flux:button x-on:click="copyUa()" icon="document-duplicate" size="xs" variant="ghost">
                    <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                </flux:button>
            </div>
            <pre class="overflow-x-auto rounded-md bg-zinc-100 p-4 text-xs dark:bg-zinc-700"><code x-text="ua"></code></pre>
        </div>
    </div>
</x-layouts.app>
