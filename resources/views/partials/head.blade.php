@php
    $seo = \App\Support\Seo::forCurrent();
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#fafafa" />
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#18181b" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>{{ $title ?? $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}" />
<link rel="canonical" href="{{ $seo['canonical'] }}" />

<meta property="og:type" content="{{ $seo['type'] }}" />
<meta property="og:site_name" content="{{ $seo['site_name'] }}" />
<meta property="og:title" content="{{ $title ?? $seo['title'] }}" />
<meta property="og:description" content="{{ $seo['description'] }}" />
<meta property="og:url" content="{{ $seo['canonical'] }}" />
<meta property="og:image" content="{{ $seo['og_image'] }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="{{ $seo['site_name'] }} — free online tools" />
<meta property="og:locale" content="{{ $seo['locale'] }}" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $title ?? $seo['title'] }}" />
<meta name="twitter:description" content="{{ $seo['description'] }}" />
<meta name="twitter:image" content="{{ $seo['og_image'] }}" />
<meta name="twitter:image:alt" content="{{ $seo['site_name'] }} — free online tools" />

<link rel="icon" href="/favicon.ico" sizes="any" />
<link rel="icon" type="image/png" href="/fav.png" />
<link rel="apple-touch-icon" href="/fav.png" />

@foreach ($seo['json_ld'] as $entry)
    <script type="application/ld+json">{!! json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600&family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet" />

{{-- Libre Barcode 128 is only needed on the barcode generator; that view pushes it here. --}}
@stack('head')

@livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
