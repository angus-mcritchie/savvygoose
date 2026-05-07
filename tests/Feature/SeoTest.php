<?php

test('the dashboard exposes site-level SEO tags', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('<meta name="description"', false)
        ->assertSee('<link rel="canonical"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('name="twitter:card"', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('Savvy Goose', false);
});

test('every tool page sets a unique title and description', function () {
    foreach (config('tools.tools') as $tool) {
        $response = $this->get('/'.$tool['slug']);

        $response->assertOk()
            ->assertSee('<title>'.e($tool['meta']['title']).' — Savvy Goose</title>', false)
            ->assertSee('content="'.e($tool['meta']['description']).'"', false)
            ->assertSee('<link rel="canonical" href="'.config('app.url').'/'.$tool['slug'].'"', false);
    }
});

test('tool pages render breadcrumb and webapp JSON-LD', function () {
    $response = $this->get('/barcode-generator');

    $response->assertOk()
        ->assertSee('"@type":"BreadcrumbList"', false)
        ->assertSee('"@type":"WebApplication"', false)
        ->assertSee('"@type":"FAQPage"', false);
});

test('tool pages render the howto and FAQ content sections', function () {
    $response = $this->get('/character-counter');

    $response->assertOk()
        ->assertSee('How to use the Character Counter')
        ->assertSee('Frequently asked questions')
        ->assertSee('How are characters counted?');
});

test('category pages set category-specific SEO', function () {
    $response = $this->get('/text');

    $response->assertOk()
        ->assertSee('<title>Text &amp; Writing Tools — Savvy Goose</title>', false)
        ->assertSee('"@type":"BreadcrumbList"', false);
});

test('the sitemap lists all routes', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('application/xml');

    $xml = $response->getContent();
    $base = rtrim(config('app.url'), '/');

    expect($xml)->toContain('<loc>'.$base.'/</loc>');

    foreach (array_keys(config('tools.categories')) as $key) {
        expect($xml)->toContain('<loc>'.$base.'/'.$key.'</loc>');
    }

    foreach (config('tools.tools') as $tool) {
        expect($xml)->toContain('<loc>'.$base.'/'.$tool['slug'].'</loc>');
    }
});

test('robots.txt references the sitemap', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)->toContain('Sitemap:')
        ->and($robots)->toContain('/sitemap.xml');
});
