<x-layouts.app>
    <div class="mx-auto max-w-3xl">
        <flux:heading level="1" size="xl" class="mb-2">Privacy</flux:heading>
        <flux:heading level="2" class="mb-8 font-normal opacity-70">What happens to your data when you use Savvy Goose. The short version: almost nothing.</flux:heading>

        <div class="grid gap-8 leading-relaxed text-zinc-700 dark:text-zinc-300">
            <div>
                <flux:heading level="3" size="lg" class="mb-3">Tools run in your browser</flux:heading>
                <p>
                    The utilities on this site are built as JavaScript that runs on your own device. Text you type,
                    files you drop in, and the results they produce are usually processed locally. Choosing a file
                    does not upload it to Savvy Goose.
                </p>
            </div>

            <div>
                <flux:heading level="3" size="lg" class="mb-3">Share links contain your input</flux:heading>
                <p>
                    Some tools put short inputs and settings in the page URL so you can share the result. Anyone
                    with that link can read those values. Opening a shared link also sends its URL to the web server
                    as part of the normal page request, so do not put passwords, private tokens, or other sensitive
                    data in a share link.
                </p>
            </div>

            <div>
                <flux:heading level="3" size="lg" class="mb-3">No accounts, no database</flux:heading>
                <p>
                    There's no sign-up and no user database. The site doesn't set advertising or cross-site
                    tracking cookies. A standard session cookie may be used for basic site function, nothing more.
                </p>
            </div>

            <div>
                <flux:heading level="3" size="lg" class="mb-3">The tools that do talk to a server</flux:heading>
                <p class="mb-3">A couple of tools genuinely need a network request to do their job:</p>
                <ul class="ml-5 grid list-disc gap-2">
                    <li>
                        <strong>GitHub Dependency Starrer</strong> signs you into GitHub with OAuth so it can star
                        repositories for you. The access token is kept only in your session for the length of your
                        visit, is never written to a database, and disconnecting both clears it and revokes the
                        authorization on GitHub's side. It also looks up packages against the npm and Packagist
                        registries and the GitHub API to match them to repositories.
                    </li>
                    <li>
                        <strong>Time Between Dates</strong> can fetch a country's public-holiday list from this
                        site's own endpoint. Only the country and year range are sent, never your dates.
                    </li>
                </ul>
            </div>

            <div>
                <flux:heading level="3" size="lg" class="mb-3">Operational data</flux:heading>
                <p>
                    The hosting platform and application monitoring may process standard request information such
                    as your IP address, requested URL, browser headers, timing, and error data. It is used to operate
                    and debug the site, not to build advertising profiles.
                </p>
            </div>

            <div>
                <flux:heading level="3" size="lg" class="mb-3">Questions</flux:heading>
                <p>
                    If anything here is unclear, <a href="{{ route('contact') }}" wire:navigate class="underline underline-offset-4">get in touch</a>.
                    The site is open source, so you can also read exactly what it does on
                    <a href="https://github.com/angus-mcritchie/savvygoose" target="_blank" rel="noopener" class="underline underline-offset-4">GitHub</a>.
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
