<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;

class Seo
{
    public static function forCurrent(): array
    {
        $routeName = Request::route()?->getName();

        return self::forRoute($routeName);
    }

    public static function forRoute(?string $routeName): array
    {
        $site = config('tools.site');
        $siteName = $site['name'];
        $siteUrl = rtrim(config('app.url'), '/');
        $ogImage = $siteUrl.$site['og_image'];

        $base = [
            'site_name' => $siteName,
            'og_image' => $ogImage,
            'locale' => str_replace('_', '-', app()->getLocale()),
            'type' => 'website',
        ];

        if ($routeName === 'dashboard' || $routeName === null) {
            return array_merge($base, [
                'title' => $siteName.' — '.$site['tagline'],
                'description' => $site['description'],
                'canonical' => $siteUrl.'/',
                'breadcrumbs' => [],
                'json_ld' => [self::websiteJsonLd($siteUrl, $siteName)],
            ]);
        }

        if (str_starts_with($routeName, 'category.')) {
            $key = substr($routeName, 9);
            $label = config('tools.categories.'.$key);
            $seo = config('tools.category_seo.'.$key, []);
            $title = $seo['title'] ?? $label;
            $description = $seo['description'] ?? "Free $label tools.";
            $url = $siteUrl.'/'.$key;

            return array_merge($base, [
                'title' => $title.' — '.$siteName,
                'description' => $description,
                'canonical' => $url,
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => $label, 'url' => $url],
                ],
                'json_ld' => [self::breadcrumbJsonLd([
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => $label, 'url' => $url],
                ])],
            ]);
        }

        $tool = collect(config('tools.tools'))->firstWhere('slug', $routeName);
        if ($tool) {
            $meta = $tool['meta'] ?? [];
            $metaTitle = $meta['title'] ?? $tool['name'];
            $description = $meta['description'] ?? $tool['tagline'];
            $url = $siteUrl.'/'.$tool['slug'];
            $categoryLabel = config('tools.categories.'.$tool['category']);
            $categoryUrl = $siteUrl.'/'.$tool['category'];

            $breadcrumbs = [
                ['name' => 'Home', 'url' => $siteUrl.'/'],
                ['name' => $categoryLabel, 'url' => $categoryUrl],
                ['name' => $tool['name'], 'url' => $url],
            ];

            $jsonLd = [
                self::breadcrumbJsonLd($breadcrumbs),
                self::webApplicationJsonLd($tool, $url, $description),
            ];

            if (! empty($tool['faqs'])) {
                $jsonLd[] = self::faqJsonLd($tool['faqs']);
            }

            return array_merge($base, [
                'title' => $metaTitle.' — '.$siteName,
                'description' => $description,
                'canonical' => $url,
                'breadcrumbs' => $breadcrumbs,
                'json_ld' => $jsonLd,
            ]);
        }

        $staticPages = [
            'about' => [
                'title' => 'About',
                'description' => 'Savvy Goose is a free kit of browser-based utility tools. No sign-up, no accounts, and your input never leaves your device.',
            ],
            'privacy' => [
                'title' => 'Privacy',
                'description' => 'How Savvy Goose handles your data: tools run in your browser, there are no accounts, and nothing you type is uploaded or stored.',
            ],
            'contact' => [
                'title' => 'Contact',
                'description' => 'Report a bug, request a tool, or contribute to Savvy Goose on GitHub.',
            ],
        ];

        if (isset($staticPages[$routeName])) {
            $page = $staticPages[$routeName];
            $url = $siteUrl.'/'.$routeName;
            $crumbs = [
                ['name' => 'Home', 'url' => $siteUrl.'/'],
                ['name' => $page['title'], 'url' => $url],
            ];

            return array_merge($base, [
                'title' => $page['title'].' — '.$siteName,
                'description' => $page['description'],
                'canonical' => $url,
                'breadcrumbs' => $crumbs,
                'json_ld' => [self::breadcrumbJsonLd($crumbs)],
            ]);
        }

        return array_merge($base, [
            'title' => $siteName.' — '.$site['tagline'],
            'description' => $site['description'],
            // Path only (no query string) so a fallthrough route still gets a
            // clean self-referencing canonical, matching the tool/category branches.
            'canonical' => rtrim($siteUrl.'/'.ltrim(Request::path(), '/'), '/'),
            'breadcrumbs' => [],
            'json_ld' => [],
        ]);
    }

    private static function websiteJsonLd(string $siteUrl, string $siteName): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl.'/',
        ];
    }

    private static function breadcrumbJsonLd(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(function ($crumb, $i) {
                return [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'],
                ];
            }, $crumbs, array_keys($crumbs)),
        ];
    }

    private static function webApplicationJsonLd(array $tool, string $url, string $description): array
    {
        // Conventional schema.org applicationCategory values (the human labels
        // like "Data & Encoding" aren't recognised values).
        $appCategories = [
            'dev' => 'DeveloperApplication',
            'diagrams' => 'DesignApplication',
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => $tool['name'],
            'description' => $description,
            'url' => $url,
            'applicationCategory' => $appCategories[$tool['category']] ?? 'UtilitiesApplication',
            'operatingSystem' => 'Any',
            'browserRequirements' => 'Requires JavaScript',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ];
    }

    private static function faqJsonLd(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $f['a'],
                ],
            ], $faqs),
        ];
    }
}
