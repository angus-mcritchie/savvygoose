<?php

/*
 * The rc-header-card pages are internal sticker tools. They were unlisted (no
 * registry entry, no nav, no sitemap) but still returned a plain indexable 200,
 * so a crawler reaching the URL any other way could index them. Unlisted is not
 * unindexable; these tests hold the difference.
 */

test('every private page is reachable and marked noindex', function () {
    foreach (config('tools.private_pages') as $page) {
        $this->get('/'.$page)
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow" />', false);
    }
});

test('public pages do not emit a robots tag', function () {
    foreach (['/', '/text', '/timestamp-converter', '/about'] as $path) {
        $this->get($path)
            ->assertOk()
            ->assertDontSee('name="robots"', false);
    }
});

test('private pages stay out of the sitemap', function () {
    $xml = $this->get('/sitemap.xml')->getContent();

    foreach (config('tools.private_pages') as $page) {
        expect($xml)->not->toContain('<loc>'.rtrim(config('app.url'), '/').'/'.$page.'</loc>');
    }
});

test('private pages are not linked from the public site', function () {
    $body = $this->get('/')->getContent();

    foreach (config('tools.private_pages') as $page) {
        expect($body)->not->toContain('/'.$page);
    }
});

test('a private page is not registered as a tool', function () {
    $slugs = array_column(config('tools.tools'), 'slug');

    foreach (config('tools.private_pages') as $page) {
        expect($slugs)->not->toContain($page);
    }
});
