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
