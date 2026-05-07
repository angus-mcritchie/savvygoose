<x-layouts.app>

    <div class="mx-auto max-w-[1200px]" x-data="jwtDecoder">
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.key class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">JWT Decoder</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Inspect a JSON Web Token's header and payload. No signature verification.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-1">
                        <flux:label>Token</flux:label>
                        <flux:dropdown position="bottom" align="start">
                            <flux:button icon="information-circle" variant="ghost" size="sm" aria-label="What is a JWT?" />
                            <flux:popover class="max-w-sm">
                                <flux:heading size="sm">Anatomy of a JWT</flux:heading>
                                <p class="mt-2 text-sm">A JWT is three Base64url-encoded segments joined by dots:</p>
                                <p class="mt-2 font-mono text-xs break-all">header.payload.signature</p>
                                <flux:separator class="my-3" />
                                <ul class="space-y-1 text-sm">
                                    <li><strong>Header</strong>: algorithm &amp; token type.</li>
                                    <li><strong>Payload</strong>: the claims (who, what, when).</li>
                                    <li><strong>Signature</strong>: proves the token wasn't tampered with.</li>
                                </ul>
                                <flux:separator class="my-3" />
                                <p class="text-sm">This tool only <strong>decodes</strong>. It doesn't verify the signature. Decoding requires no secret; verifying does.</p>
                            </flux:popover>
                        </flux:dropdown>
                    </div>
                    <flux:button x-on:click="clear()" x-bind:disabled="!token" icon="trash" size="sm" variant="filled">
                        Clear
                    </flux:button>
                </div>
                <flux:textarea
                    name="token"
                    x-model="token"
                    placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.signature"
                    rows="6"
                    class="font-mono"
                />
                <p
                    x-show="parseError"
                    x-cloak
                    x-text="parseError"
                    class="mt-3 text-sm text-red-600 dark:text-red-400"
                ></p>

                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                    <span class="opacity-70">Decoding only.</span>
                    <span class="opacity-70">We never verify the signature. Anyone holding the secret could mint a token that decodes the same way.</span>
                </div>
            </div>

            <template x-if="payloadJson">
                <div class="grid gap-4 sm:grid-cols-2">
                    <template x-if="expiryStatus">
                        <div
                            class="rounded-lg border p-4"
                            :class="expiryStatus.expired
                                ? 'border-red-500/40 bg-red-500/10 text-red-700 dark:text-red-300'
                                : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'"
                        >
                            <flux:subheading class="!text-current">
                                <span x-text="expiryStatus.expired ? 'Expired' : 'Active'"></span>
                            </flux:subheading>
                            <p class="mt-1 text-sm">
                                <span x-text="expiryStatus.expired ? 'Expired ' : 'Expires '"></span>
                                <span x-text="expiryStatus.relative"></span>
                                <span class="opacity-70" x-text="' (' + expiryStatus.absolute + ')'"></span>
                            </p>
                        </div>
                    </template>
                    <template x-if="notYetActive">
                        <div class="rounded-lg border border-amber-500/40 bg-amber-500/10 p-4 text-amber-700 dark:text-amber-300">
                            <flux:subheading class="!text-current">Not yet active</flux:subheading>
                            <p class="mt-1 text-sm">The <code>nbf</code> claim is in the future. Most servers will reject this token until then.</p>
                        </div>
                    </template>
                </div>
            </template>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <div class="mb-4 flex items-center justify-between border-b border-black/10 pb-4 dark:border-white/10">
                        <flux:heading size="lg">Header</flux:heading>
                        <flux:button x-on:click="copy('header')" x-bind:disabled="!prettyHeader" icon="document-duplicate" size="xs" variant="ghost">
                            <span x-text="copiedHeader ? 'Copied!' : 'Copy'">Copy</span>
                        </flux:button>
                    </div>
                    <template x-if="prettyHeader">
                        <pre class="overflow-x-auto rounded-md bg-zinc-100 p-4 text-sm dark:bg-zinc-700"><code x-text="prettyHeader"></code></pre>
                    </template>
                    <template x-if="!prettyHeader">
                        <p class="text-sm opacity-60">Paste a token to see its header.</p>
                    </template>
                </div>

                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <div class="mb-4 flex items-center justify-between border-b border-black/10 pb-4 dark:border-white/10">
                        <flux:heading size="lg">Payload</flux:heading>
                        <flux:button x-on:click="copy('payload')" x-bind:disabled="!prettyPayload" icon="document-duplicate" size="xs" variant="ghost">
                            <span x-text="copiedPayload ? 'Copied!' : 'Copy'">Copy</span>
                        </flux:button>
                    </div>
                    <template x-if="prettyPayload">
                        <pre class="overflow-x-auto rounded-md bg-zinc-100 p-4 text-sm dark:bg-zinc-700"><code x-text="prettyPayload"></code></pre>
                    </template>
                    <template x-if="!prettyPayload">
                        <p class="text-sm opacity-60">Paste a token to see its payload.</p>
                    </template>
                </div>
            </div>

            <template x-if="standardClaims.length || otherClaims.length">
                <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                    <flux:heading class="mb-6 border-b border-black/10 pb-4 dark:border-white/10" size="lg">Claims</flux:heading>

                    <template x-if="standardClaims.length">
                        <div class="mb-6">
                            <flux:subheading class="mb-3">Registered claims</flux:subheading>
                            <dl class="grid gap-3 sm:grid-cols-[auto_1fr] sm:gap-x-6">
                                <template x-for="claim in standardClaims" :key="claim.key">
                                    <div class="contents">
                                        <dt class="text-sm font-medium">
                                            <span x-text="claim.label"></span>
                                            <code class="ml-2 text-xs opacity-60" x-text="claim.key"></code>
                                        </dt>
                                        <dd class="text-sm">
                                            <div class="font-mono break-all" x-text="claim.value"></div>
                                            <div class="text-xs opacity-60" x-show="claim.helper" x-text="claim.helper"></div>
                                        </dd>
                                    </div>
                                </template>
                            </dl>
                        </div>
                    </template>

                    <template x-if="otherClaims.length">
                        <div>
                            <flux:subheading class="mb-3">Other claims</flux:subheading>
                            <dl class="grid gap-3 sm:grid-cols-[auto_1fr] sm:gap-x-6">
                                <template x-for="claim in otherClaims" :key="claim.key">
                                    <div class="contents">
                                        <dt class="font-mono text-sm font-medium" x-text="claim.key"></dt>
                                        <dd class="font-mono text-sm break-all" x-text="claim.value"></dd>
                                    </div>
                                </template>
                            </dl>
                        </div>
                    </template>
                </div>
            </template>

            <div class="rounded-lg border border-black/10 p-6 dark:border-white/10">
                <flux:subheading>Signature</flux:subheading>
                <p class="mt-2 break-all font-mono text-xs opacity-70" x-text="signature || '(none)'"></p>
            </div>
        </div>
    </div>
</x-layouts.app>
