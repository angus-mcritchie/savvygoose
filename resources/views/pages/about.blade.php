<x-layouts.app>
    <div class="mx-auto max-w-3xl">
        <flux:heading level="1" size="xl" class="mb-2">About Savvy Goose</flux:heading>
        <flux:heading level="2" class="mb-8 font-normal opacity-70">A small kit of free online tools that run entirely in your browser.</flux:heading>

        <div class="grid gap-6 leading-relaxed text-zinc-700 dark:text-zinc-300">
            <p>
                Savvy Goose is a collection of everyday utilities: barcode and QR generators, a JSON formatter,
                a percentage calculator, encoders and decoders, and a growing list of others. They're free,
                there's no sign-up, and there are no ads.
            </p>
            <p>
                The thing that makes them different is where the work happens. Almost every tool here runs
                as JavaScript in your own browser. When you paste text into the character counter or drop an
                image into the resizer, that data is processed on your device and never sent to a server.
                It's faster, it works offline once the page has loaded, and it means your input stays yours. Some
                tools can place short inputs in an optional share URL; the <a href="{{ route('privacy') }}" wire:navigate class="underline underline-offset-4">privacy page</a>
                explains that exception.
            </p>
            <p>
                There's no database and no user accounts behind the site, on purpose. Fewer moving parts means
                fewer things to break, less to maintain, and nothing quietly collecting your data. The one
                exception is the GitHub Dependency Starrer, which signs you into GitHub so it can star repos on
                your behalf. That's spelled out on the <a href="{{ route('privacy') }}" wire:navigate class="underline underline-offset-4">privacy page</a>.
            </p>
            <p>
                The whole project is open source. If you spot a bug, want a new tool, or just want to see how
                something works, the code is on
                <a href="https://github.com/angus-mcritchie/savvygoose" target="_blank" rel="noopener" class="underline underline-offset-4">GitHub</a>
                and pull requests are welcome.
            </p>
        </div>

        <div class="mt-10 flex flex-wrap gap-3">
            <flux:button href="{{ route('dashboard') }}" wire:navigate icon="squares-2x2">Browse the tools</flux:button>
            <flux:button href="{{ route('contact') }}" wire:navigate variant="ghost">Get in touch</flux:button>
        </div>
    </div>
</x-layouts.app>
