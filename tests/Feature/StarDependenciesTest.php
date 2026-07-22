<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

test('resolve returns a github repo for an npm dependency', function () {
    Http::fake([
        'registry.npmjs.org/laravel-mix/latest' => Http::response([
            'repository' => ['type' => 'git', 'url' => 'git+https://github.com/laravel-mix/laravel-mix.git'],
        ]),
    ]);

    $response = $this->postJson('/api/star-dependencies/resolve', [
        'manifest' => json_encode(['dependencies' => ['laravel-mix' => '^6.0']]),
        'type' => 'npm',
    ]);

    $response->assertOk()->assertJson([
        'dependencies' => [
            ['name' => 'laravel-mix', 'owner' => 'laravel-mix', 'repo' => 'laravel-mix', 'resolved' => true],
        ],
    ]);
});

test('resolve url-encodes scoped npm package names', function () {
    Http::fake([
        'registry.npmjs.org/%40mui%2Fmaterial/latest' => Http::response([
            'repository' => ['type' => 'git', 'url' => 'git+https://github.com/mui/material-ui.git', 'directory' => 'packages/mui-material'],
        ]),
    ]);

    $response = $this->postJson('/api/star-dependencies/resolve', [
        'manifest' => json_encode(['dependencies' => ['@mui/material' => '^9.0.0']]),
        'type' => 'npm',
    ]);

    $response->assertOk()->assertJson([
        'dependencies' => [
            ['name' => '@mui/material', 'owner' => 'mui', 'repo' => 'material-ui', 'resolved' => true],
        ],
    ]);

    Http::assertSent(fn ($request) => $request->url() === 'https://registry.npmjs.org/%40mui%2Fmaterial/latest');
});

test('resolve returns a github repo for a composer dependency', function () {
    Http::fake([
        'repo.packagist.org/p2/laravel/framework.json' => Http::response([
            'packages' => [
                'laravel/framework' => [
                    ['source' => ['type' => 'git', 'url' => 'https://github.com/laravel/framework.git']],
                ],
            ],
        ]),
    ]);

    $response = $this->postJson('/api/star-dependencies/resolve', [
        'manifest' => json_encode(['require' => ['php' => '^8.4', 'laravel/framework' => '^13.0']]),
        'type' => 'composer',
    ]);

    $response->assertOk()->assertJson([
        'dependencies' => [
            ['name' => 'laravel/framework', 'owner' => 'laravel', 'repo' => 'framework', 'resolved' => true],
        ],
    ]);
});

test('resolve marks a dependency unresolved when the registry has no github url', function () {
    Http::fake([
        'registry.npmjs.org/*' => Http::response(['repository' => null]),
    ]);

    $response = $this->postJson('/api/star-dependencies/resolve', [
        'manifest' => json_encode(['dependencies' => ['some-package' => '^1.0']]),
        'type' => 'npm',
    ]);

    $response->assertOk()->assertJson([
        'dependencies' => [
            ['name' => 'some-package', 'owner' => null, 'repo' => null, 'resolved' => false],
        ],
    ]);
});

test('resolve rejects invalid json', function () {
    $this->postJson('/api/star-dependencies/resolve', [
        'manifest' => 'not json',
        'type' => 'npm',
    ])->assertStatus(422);
});

test('resolve rejects a manifest with no dependencies', function () {
    $this->postJson('/api/star-dependencies/resolve', [
        'manifest' => json_encode(['name' => 'my-app']),
        'type' => 'npm',
    ])->assertStatus(422);
});

test('star requires an active github connection', function () {
    $this->postJson('/api/star-dependencies/star', [
        'repos' => [['owner' => 'laravel', 'repo' => 'framework']],
    ])->assertStatus(403);
});

test('star repos when connected to github', function () {
    Http::fake([
        'api.github.com/user/starred/*' => Http::response('', 204),
    ]);

    $response = $this->withSession(['github_token' => 'test-token'])
        ->postJson('/api/star-dependencies/star', [
            'repos' => [['owner' => 'laravel', 'repo' => 'framework']],
        ]);

    $response->assertOk()->assertJson([
        'results' => [
            ['owner' => 'laravel', 'repo' => 'framework', 'starred' => true],
        ],
    ]);
});

test('disconnect clears the local token even when github revocation fails', function () {
    Http::fake(fn () => throw new ConnectionException('GitHub is unavailable'));

    $this->withSession(['github_token' => 'test-token'])
        ->postJson('/auth/github/disconnect')
        ->assertOk()
        ->assertJson([
            'disconnected' => true,
            'revoked' => false,
        ]);

    expect(session('github_token'))->toBeNull();
});
