<?php

return [

    'categories' => [
        'text' => 'Text & Writing',
        'data' => 'Data & Encoding',
        'numbers' => 'Numbers & Time',
        'generators' => 'Generators',
    ],

    'tools' => [
        [
            'slug' => 'character-counter',
            'name' => 'Character Counter',
            'tagline' => 'Count characters & words.',
            'category' => 'text',
            'icon' => ['type' => 'image', 'src' => 'image/keyboard.png'],
        ],
        [
            'slug' => 'markdown-converter',
            'name' => 'Markdown Converter',
            'tagline' => 'Convert between Markdown & HTML.',
            'category' => 'text',
            'icon' => ['type' => 'flux', 'name' => 'code-bracket-square'],
        ],
        [
            'slug' => 'percentage-calculator',
            'name' => 'Percentage Calculator',
            'tagline' => 'Common percentage calculations.',
            'category' => 'numbers',
            'icon' => ['type' => 'image', 'src' => 'image/discount.png'],
        ],
        [
            'slug' => 'barcode-generator',
            'name' => 'Barcode Generator',
            'tagline' => 'Generate & print Code 128 barcodes.',
            'category' => 'generators',
            'icon' => ['type' => 'image', 'src' => 'image/barcode.png'],
        ],
    ],

];
