<?php

/*
 * The sitemap's <lastmod> used to be derived from filemtime(). Laravel Cloud
 * deploys from a fresh checkout, so every file carried the deploy timestamp and
 * all 40 URLs shared one date that moved on every deploy. Google discards a
 * lastmod that behaves like that. These tests pin the replacement: dates are
 * declared in config/tools.php, and an undeclared or malformed one omits the
 * element rather than inventing a value.
 */

function sitemapLastmods(string $xml): array
{
    preg_match_all('#<url>\s*<loc>([^<]+)</loc>(.*?)</url>#s', $xml, $matches, PREG_SET_ORDER);

    $found = [];
    foreach ($matches as $match) {
        $found[$match[1]] = preg_match('#<lastmod>([^<]+)</lastmod>#', $match[2], $m) ? $m[1] : null;
    }

    return $found;
}

test('every tool declares a real calendar date in updated', function () {
    foreach (config('tools.tools') as $tool) {
        expect(isset($tool['updated']))->toBeTrue("{$tool['slug']} is missing an 'updated' date");

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $tool['updated']);

        expect($parsed && $parsed->format('Y-m-d') === $tool['updated'])
            ->toBeTrue("{$tool['slug']} has an invalid 'updated' date: {$tool['updated']}");
    }
});

test('every static page declares a real calendar date', function () {
    foreach (config('tools.static_pages') as $page => $updated) {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $updated);

        expect($parsed && $parsed->format('Y-m-d') === $updated)
            ->toBeTrue("$page has an invalid date: $updated");
    }
});

test('each tool url carries the lastmod declared in config', function () {
    $lastmods = sitemapLastmods($this->get('/sitemap.xml')->getContent());
    $base = rtrim(config('app.url'), '/');

    foreach (config('tools.tools') as $tool) {
        expect($lastmods[$base.'/'.$tool['slug']] ?? null)->toBe($tool['updated']);
    }
});

test('a category is as fresh as the newest tool it lists', function () {
    $lastmods = sitemapLastmods($this->get('/sitemap.xml')->getContent());
    $base = rtrim(config('app.url'), '/');
    $tools = collect(config('tools.tools'));

    foreach (array_keys(config('tools.categories')) as $key) {
        $newest = $tools->where('category', $key)->max('updated');

        expect($lastmods[$base.'/'.$key] ?? null)->toBe($newest);
    }
});

test('the homepage is as fresh as the newest tool on the site', function () {
    $lastmods = sitemapLastmods($this->get('/sitemap.xml')->getContent());
    $base = rtrim(config('app.url'), '/');

    expect($lastmods[$base.'/'] ?? null)->toBe(collect(config('tools.tools'))->max('updated'));
});

/*
 * The regression guard. The old bug's signature was not "a wrong date" but
 * "one date everywhere", which no per-URL assertion above would catch if the
 * registry ever collapsed back to a single derived value.
 */
test('the sitemap does not stamp every url with one identical date', function () {
    $lastmods = array_filter(sitemapLastmods($this->get('/sitemap.xml')->getContent()));

    expect(count(array_unique($lastmods)))->toBeGreaterThan(1);
});

/*
 * The static-page dates are a lookup keyed by page, not the list of pages. If
 * they were the list, a typo in config would silently drop a real page from the
 * sitemap and advertise a 404 in its place — a worse failure than a missing date.
 */
test('a mistyped static page date key cannot change which urls the sitemap lists', function () {
    $base = rtrim(config('app.url'), '/');
    $before = array_keys(sitemapLastmods($this->get('/sitemap.xml')->getContent()));

    config(['tools.static_pages' => ['about' => '2026-07-23', 'privcy' => '2026-07-23']]);
    $lastmods = sitemapLastmods($this->get('/sitemap.xml')->getContent());

    expect(array_keys($lastmods))->toBe($before)
        ->and($lastmods)->not->toHaveKey($base.'/privcy')
        ->and($lastmods[$base.'/privacy'])->toBeNull()
        ->and($lastmods[$base.'/about'])->toBe('2026-07-23');
});

/*
 * The omit-on-bad-date path is a safety net, not a licence to ship undated URLs.
 * As shipped, every URL should carry a date; anything without one is a page whose
 * 'updated' was forgotten. This asserts the whole sitemap at once, so it catches a
 * missing static-page date and a tool added without 'updated' alike.
 */
test('every url in the shipped sitemap carries a lastmod', function () {
    $lastmods = sitemapLastmods($this->get('/sitemap.xml')->getContent());

    $undated = array_keys(array_filter($lastmods, fn ($date) => $date === null));

    expect($undated)->toBe([])
        ->and(count($lastmods))->toBe(40);
});

test('every static page date key names a page the sitemap actually lists', function () {
    $base = rtrim(config('app.url'), '/');
    $listed = array_keys(sitemapLastmods($this->get('/sitemap.xml')->getContent()));

    foreach (array_keys(config('tools.static_pages')) as $page) {
        expect(in_array($base.'/'.$page, $listed, true))
            ->toBeTrue("static_pages key '$page' is not a sitemap URL");
    }
});

test('a non-string updated value omits lastmod instead of throwing', function () {
    $tools = config('tools.tools');
    $tools[0]['updated'] = [];
    $tools[1]['updated'] = 20260804;
    config(['tools.tools' => $tools]);

    $response = $this->get('/sitemap.xml');
    $response->assertOk();

    $lastmods = sitemapLastmods($response->getContent());
    $base = rtrim(config('app.url'), '/');

    expect($lastmods[$base.'/'.$tools[0]['slug']])->toBeNull()
        ->and($lastmods[$base.'/'.$tools[1]['slug']])->toBeNull();
});

test('a tool with no usable updated date omits lastmod instead of inventing one', function () {
    $tools = config('tools.tools');
    $tools[0]['updated'] = '2026-13-45';
    unset($tools[1]['updated']);
    config(['tools.tools' => $tools]);

    $lastmods = sitemapLastmods($this->get('/sitemap.xml')->getContent());
    $base = rtrim(config('app.url'), '/');

    expect($lastmods[$base.'/'.$tools[0]['slug']])->toBeNull()
        ->and($lastmods[$base.'/'.$tools[1]['slug']])->toBeNull();
});
