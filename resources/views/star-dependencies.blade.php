<x-layouts.app>
    <div
        class="mx-auto max-w-[1100px]"
        x-data="starDependencies({{ Js::from(['connected' => session()->has('github_token')]) }})"
    >
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.star class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">GitHub Dependency Starrer</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Star every GitHub repo behind your project's dependencies.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10">
                <flux:heading class="mb-2" size="xl">1. Manifest</flux:heading>
                <flux:subheading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10">
                    Paste a <code>package.json</code> or <code>composer.json</code>, or upload one. Nothing is
                    stored: the manifest is parsed just long enough to resolve each dependency's GitHub repo.
                </flux:subheading>

                <div class="grid gap-4">
                    <flux:textarea
                        x-model="manifestText"
                        label="Manifest contents"
                        placeholder='{ "dependencies": { "laravel/framework": "^13.0" } }'
                        rows="10"
                    />

                    <div class="flex flex-wrap items-center gap-3">
                        <input type="file" accept=".json,application/json" x-ref="picker" x-on:change="onFileSelected($event)" class="hidden" />
                        <flux:button x-on:click="$refs.picker.click()" icon="arrow-up-tray" size="sm" variant="filled">
                            Upload file
                        </flux:button>
                        <flux:button
                            x-on:click="resolve()"
                            x-bind:disabled="!manifestText || resolving"
                            icon="magnifying-glass"
                            size="sm"
                        >
                            <span x-show="!resolving">Resolve dependencies</span>
                            <span x-show="resolving" x-cloak>Resolving…</span>
                        </flux:button>
                        <flux:text size="sm" class="text-red-600 dark:text-red-400" x-show="fileError" x-cloak x-text="fileError" />
                    </div>

                    <flux:callout x-show="error" x-cloak variant="danger" icon="exclamation-triangle">
                        <span x-text="error"></span>
                    </flux:callout>
                </div>
            </div>

            <div class="rounded-lg border border-black/10 p-6 sm:p-8 dark:border-white/10" x-show="dependencies.length" x-cloak>
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-black/10 pb-4 dark:border-white/10">
                    <div>
                        <flux:heading size="xl">2. Repos</flux:heading>
                        <flux:subheading x-text="`${resolvableCount} of ${dependencies.length} resolved to a GitHub repo.`" />
                    </div>
                    <div class="flex gap-2">
                        <flux:button x-on:click="toggleAll(true)" size="xs" variant="ghost">Select all</flux:button>
                        <flux:button x-on:click="toggleAll(false)" size="xs" variant="ghost">Select none</flux:button>
                    </div>
                </div>

                <div class="grid gap-2">
                    <template x-for="dep in dependencies" x-bind:key="dep.name">
                        <div class="flex items-center gap-3 rounded-md px-2 py-1.5" x-bind:class="dep.resolved ? '' : 'opacity-50'">
                            <flux:checkbox x-model="dep.selected" x-bind:disabled="!dep.resolved" />
                            <span class="w-full max-w-[260px] truncate font-mono text-sm" x-text="dep.name"></span>
                            <span class="flex-1 truncate text-sm opacity-70" x-text="dep.resolved ? `${dep.owner}/${dep.repo}` : 'Could not resolve a GitHub repo'"></span>
                            <flux:badge x-show="dep.status === 'starred'" x-cloak color="lime" size="sm">Starred</flux:badge>
                            <flux:badge x-show="dep.status === 'failed'" x-cloak color="red" size="sm">Failed</flux:badge>
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-black/10 pt-6 dark:border-white/10">
                    <flux:button x-show="!connected" x-cloak x-on:click="connectGithub()" icon="link" variant="primary">
                        Connect GitHub
                    </flux:button>
                    <template x-if="connected">
                        <div class="flex flex-wrap items-center gap-3">
                            <flux:button
                                x-on:click="starSelected()"
                                x-bind:disabled="!selected.length || starring"
                                icon="star"
                                variant="primary"
                            >
                                <span x-show="!starring" x-text="`Star selected (${selected.length})`"></span>
                                <span x-show="starring" x-cloak>Starring…</span>
                            </flux:button>
                            <flux:button x-on:click="disconnect()" icon="link-slash" size="sm" variant="ghost">
                                Disconnect
                            </flux:button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
