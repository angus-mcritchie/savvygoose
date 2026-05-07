@php
    $categories = config('tools.categories');
    $tools = collect(config('tools.tools'));
@endphp

<div
    class="contents"
    x-data
    x-on:keydown.window.meta.k.prevent="$dispatch('modal-show', { name: 'command-palette' })"
    x-on:keydown.window.ctrl.k.prevent="$dispatch('modal-show', { name: 'command-palette' })"
>
    <flux:modal.trigger name="command-palette">
        <flux:button class="ml-auto" icon="magnifying-glass" variant="subtle">
            <span class="max-sm:hidden">{{ __('Search') }}</span>
            <kbd
                class="ms-2 hidden rounded bg-zinc-200/70 px-1.5 py-0.5 font-sans text-xs font-medium text-zinc-500 sm:inline-flex dark:bg-white/10 dark:text-zinc-400"
                x-data
                x-text="/mac/i.test(navigator.platform) ? '⌘K' : 'Ctrl K'"
            >⌘K</kbd>
        </flux:button>
    </flux:modal.trigger>

    <flux:modal class="!w-full !max-w-xl !p-0" name="command-palette" variant="bare">
        <flux:command class="!shadow-2xl">
            <flux:command.input autofocus closable placeholder="{{ __('Search tools…') }}" />

            <flux:command.items class="max-h-[60vh]">
                @foreach ($tools as $tool)
                    <flux:command.item
                        :icon="$tool['icon']['type'] === 'flux' ? $tool['icon']['name'] : null"
                        x-on:click="$dispatch('modal-close', { name: 'command-palette' }); window.Livewire.navigate('{{ route($tool['slug']) }}')"
                    >
                        <span>{{ __($tool['name']) }}</span>
                        <span class="ms-auto pl-3 text-xs font-normal text-zinc-500 max-sm:hidden dark:text-zinc-400">
                            {{ __($categories[$tool['category']] ?? '') }}
                        </span>
                    </flux:command.item>
                @endforeach
            </flux:command.items>
        </flux:command>
    </flux:modal>
</div>
