<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? 'Savvy Goose' }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
<link href="https://fonts.bunny.net/css?family=Libre+Barcode+128&family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet" />

@livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
