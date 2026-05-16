<?php

return [

    'site' => [
        'name' => 'Savvy Goose',
        'tagline' => 'Free Online Tools, No Sign-up',
        'description' => 'A small kit of online tools for everyday work: barcode and QR generators, JSON formatter, percentage calculator, and more. No sign-up.',
        'og_image' => '/image/og.png',
    ],

    'categories' => [
        'text' => 'Text & Writing',
        'data' => 'Data & Encoding',
        'numbers' => 'Numbers & Time',
        'generators' => 'Generators',
    ],

    'category_seo' => [
        'text' => [
            'title' => 'Text & Writing Tools',
            'description' => 'Tools for working with text: character counter, case converter, diff viewer, and a Markdown to HTML converter. All run in your browser.',
        ],
        'data' => [
            'title' => 'Data & Encoding Tools',
            'description' => 'Encoders, decoders, and formatters for the things you reach for daily: Base64, URLs, JWTs, hashes, JSON, regex, and more.',
        ],
        'numbers' => [
            'title' => 'Number & Time Tools',
            'description' => 'Quick calculators: percentages, unit conversions, Unix timestamps, and the time between two dates.',
        ],
        'generators' => [
            'title' => 'Generators',
            'description' => 'Generators for barcodes, QR codes, passwords, UUIDs, URL slugs, and Lorem Ipsum placeholder text.',
        ],
    ],

    'tools' => [
        [
            'slug' => 'character-counter',
            'name' => 'Character Counter',
            'tagline' => 'Count characters & words.',
            'category' => 'text',
            'icon' => ['type' => 'flux', 'name' => 'hashtag'],
            'meta' => [
                'title' => 'Character & Word Counter',
                'description' => 'Counts characters, words, sentences, and reading time as you type. Free, no sign-up, runs in your browser.',
            ],
            'howto' => [
                'Paste or type text into the editor.',
                'Counts for characters, words, sentences, lines, and reading time update as you go.',
                'Use the Copy button to grab your text, or Clear to start over.',
            ],
            'faqs' => [
                ['q' => 'How are characters counted?', 'a' => 'Every Unicode code point counts as one character, including spaces and line breaks. There\'s a separate count that excludes whitespace.'],
                ['q' => 'How is reading time estimated?', 'a' => 'Reading time assumes 200 words per minute, the average for adult silent reading.'],
                ['q' => 'Is my text uploaded anywhere?', 'a' => 'No. Counting runs in your browser, so your text never leaves your device.'],
            ],
        ],
        [
            'slug' => 'case-converter',
            'name' => 'Case Converter',
            'tagline' => 'camelCase, snake_case, kebab-case & more.',
            'category' => 'text',
            'icon' => ['type' => 'flux', 'name' => 'language'],
            'meta' => [
                'title' => 'Case Converter: camelCase, snake_case & more',
                'description' => 'Converts text between camelCase, snake_case, kebab-case, PascalCase, Title Case, UPPER, and lower. Instant, browser-only.',
            ],
            'howto' => [
                'Enter or paste your text.',
                'Pick the case format you want.',
                'Copy the converted text with one click.',
            ],
            'faqs' => [
                ['q' => 'Which cases are supported?', 'a' => 'camelCase, PascalCase, snake_case, SCREAMING_SNAKE_CASE, kebab-case, Title Case, sentence case, UPPER, and lower.'],
                ['q' => 'Does it handle non-English text?', 'a' => 'Unicode letters are preserved, and diacritics are kept rather than stripped.'],
                ['q' => 'Is my text sent anywhere?', 'a' => 'No. The conversion runs locally.'],
            ],
        ],
        [
            'slug' => 'diff-viewer',
            'name' => 'Diff Viewer',
            'tagline' => 'Compare two pieces of text.',
            'category' => 'text',
            'icon' => ['type' => 'flux', 'name' => 'arrows-right-left'],
            'meta' => [
                'title' => 'Text Diff Viewer: Compare Two Texts',
                'description' => 'Drop in two pieces of text and see what changed. Additions, deletions, and edited lines are called out side-by-side.',
            ],
            'howto' => [
                'Paste the original text on the left.',
                'Paste the changed version on the right.',
                'Additions and deletions are highlighted line-by-line.',
            ],
            'faqs' => [
                ['q' => 'How is the diff computed?', 'a' => 'It\'s a line-based diff that runs locally. Lines that only differ slightly are still highlighted as changed.'],
                ['q' => 'Can I diff long files?', 'a' => 'There\'s no hard limit, but very large inputs can slow the browser down.'],
                ['q' => 'Is my text uploaded?', 'a' => 'No. Both inputs stay in your browser.'],
            ],
        ],
        [
            'slug' => 'markdown-converter',
            'name' => 'Markdown Converter',
            'tagline' => 'Convert between Markdown & HTML.',
            'category' => 'text',
            'icon' => ['type' => 'flux', 'name' => 'code-bracket-square'],
            'meta' => [
                'title' => 'Markdown to HTML Converter',
                'description' => 'Convert Markdown to HTML and HTML back to Markdown. CommonMark-compatible with GitHub-flavored extensions.',
            ],
            'howto' => [
                'Paste Markdown or HTML into the editor.',
                'Switch direction to convert either way.',
                'Copy the converted output.',
            ],
            'faqs' => [
                ['q' => 'Which Markdown flavor is supported?', 'a' => 'CommonMark with GitHub-flavored extensions, including tables and fenced code blocks.'],
                ['q' => 'Does it support tables?', 'a' => 'GFM tables convert in both directions.'],
                ['q' => 'Is my content sent to a server?', 'a' => 'No. The conversion happens locally in your browser.'],
            ],
        ],
        [
            'slug' => 'percentage-calculator',
            'name' => 'Percentage Calculator',
            'tagline' => 'Common percentage calculations.',
            'category' => 'numbers',
            'icon' => ['type' => 'flux', 'name' => 'calculator'],
            'meta' => [
                'title' => 'Percentage Calculator',
                'description' => 'Covers the percentage questions you actually run into: percent of a number, percent change, percent of total, discounts, and tips.',
            ],
            'howto' => [
                'Pick the percentage operation you need.',
                'Enter the values. The result updates as you type.',
                'Copy the result, or share the URL (calculations are URL-encoded).',
            ],
            'faqs' => [
                ['q' => 'What can it calculate?', 'a' => 'Percent of a number, percent change, percent of total, discounts, and tip splits.'],
                ['q' => 'Is the calculation private?', 'a' => 'Yes. Everything runs locally in your browser.'],
            ],
        ],
        [
            'slug' => 'unit-converter',
            'name' => 'Unit Converter',
            'tagline' => 'Length, weight, temperature & data sizes.',
            'category' => 'numbers',
            'icon' => ['type' => 'flux', 'name' => 'scale'],
            'meta' => [
                'title' => 'Unit Converter: Length, Weight, Temperature, Data',
                'description' => 'Convert between metric and imperial units. Covers length, weight, temperature, volume, area, and digital data sizes.',
            ],
            'howto' => [
                'Pick a unit category (length, weight, etc.).',
                'Choose the source and target units.',
                'Type a value. The conversion updates live.',
            ],
            'faqs' => [
                ['q' => 'Which categories are supported?', 'a' => 'Length, weight/mass, temperature, volume, area, and digital data sizes (bytes through terabytes).'],
                ['q' => 'How precise are the conversions?', 'a' => 'Conversions use standard SI factors with high-precision floating point.'],
            ],
        ],
        [
            'slug' => 'timestamp-converter',
            'name' => 'Timestamp Converter',
            'tagline' => 'Unix epoch ↔ ISO ↔ human, with timezones.',
            'category' => 'numbers',
            'icon' => ['type' => 'flux', 'name' => 'clock'],
            'meta' => [
                'title' => 'Unix Timestamp Converter (Epoch ↔ ISO ↔ Human)',
                'description' => 'Convert Unix timestamps to ISO 8601 and human-readable dates, or back the other way. Handles timezones, seconds, and milliseconds.',
            ],
            'howto' => [
                'Paste a Unix timestamp (seconds or milliseconds), or pick a date.',
                'Adjust the timezone if needed.',
                'Copy the format you need: Unix, ISO 8601, RFC 2822, or human-readable.',
            ],
            'faqs' => [
                ['q' => 'Does it support milliseconds?', 'a' => 'Yes. Both seconds and milliseconds are auto-detected, and you can switch manually.'],
                ['q' => 'Are timezones handled?', 'a' => 'Pick any IANA timezone and the conversion updates instantly.'],
            ],
        ],
        [
            'slug' => 'time-between-dates',
            'name' => 'Time Between Dates',
            'tagline' => 'Calendar days, business days & holidays between two dates.',
            'category' => 'numbers',
            'icon' => ['type' => 'flux', 'name' => 'calendar-days'],
            'meta' => [
                'title' => 'Time Between Dates: Calendar, Business Days & Holidays',
                'description' => 'Counts days, weeks, business days, and country-specific public holidays between two dates.',
            ],
            'howto' => [
                'Pick a start and end date.',
                'Optionally pick a country to count public holidays.',
                'See calendar days, weekdays, business days, and the list of holidays in range.',
            ],
            'faqs' => [
                ['q' => 'How are business days counted?', 'a' => 'Business days exclude weekends. If you pick a country, public holidays falling on weekdays are also excluded.'],
                ['q' => 'Which countries are supported for holidays?', 'a' => 'A growing list. Open the dropdown to see whether yours is in there.'],
            ],
        ],
        [
            'slug' => 'barcode-generator',
            'name' => 'Barcode Generator',
            'tagline' => 'Generate & print Code 128 barcodes.',
            'category' => 'generators',
            'icon' => ['type' => 'flux', 'name' => 'tag'],
            'meta' => [
                'title' => 'Free Barcode Generator: Code 128, Print or Share',
                'description' => 'Generates Code 128 barcodes you can print straight from the page, or share by URL. No sign-up, works on mobile.',
            ],
            'howto' => [
                'Type the value you want to encode.',
                'Adjust the size and label as needed.',
                'Hit Print, or copy the share URL. Opening it later restores the same barcode.',
            ],
            'faqs' => [
                ['q' => 'Which symbology is used?', 'a' => 'Code 128, which encodes any printable ASCII (letters, digits, and most punctuation).'],
                ['q' => 'How do shareable URLs work?', 'a' => 'Settings are encoded in the URL query string, so anyone opening the link sees the same barcode. Add ?print=true to auto-open the print dialog.'],
                ['q' => 'Is the barcode generated on a server?', 'a' => 'No. The SVG is rendered locally, so the value you type never leaves your device.'],
            ],
        ],
        [
            'slug' => 'qr-code-generator',
            'name' => 'QR Code Generator',
            'tagline' => 'Generate QR codes for URLs, text, and more.',
            'category' => 'generators',
            'icon' => ['type' => 'flux', 'name' => 'qr-code'],
            'meta' => [
                'title' => 'QR Code Generator (Free, No Sign-up)',
                'description' => 'Make QR codes for URLs, text, Wi-Fi credentials, or contacts, and download them as PNGs. No sign-up.',
            ],
            'howto' => [
                'Enter the URL or text you want to encode.',
                'Adjust size and error correction if needed.',
                'Download the QR code as a PNG.',
            ],
            'faqs' => [
                ['q' => 'Is there a size or character limit?', 'a' => 'QR codes have a maximum capacity that depends on the error correction level you pick. Plain ASCII fits the most.'],
                ['q' => 'Will it work for Wi-Fi credentials?', 'a' => 'Encode a Wi-Fi connection string and a phone\'s camera will offer to join the network.'],
                ['q' => 'Is data sent to a server?', 'a' => 'No. QR codes are generated locally in your browser.'],
            ],
        ],
        [
            'slug' => 'base64-encoder',
            'name' => 'Base64 Encoder',
            'tagline' => 'Encode & decode Base64 for text or files.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'arrows-right-left'],
            'meta' => [
                'title' => 'Base64 Encoder & Decoder (Text and Files)',
                'description' => 'Encode and decode Base64 for text or files. Everything happens locally, so the input never leaves your device.',
            ],
            'howto' => [
                'Pick text or file mode.',
                'Type or upload your input.',
                'Copy the encoded or decoded output.',
            ],
            'faqs' => [
                ['q' => 'Is URL-safe Base64 supported?', 'a' => 'Yes. Flip the URL-safe toggle if you need it for query strings or filenames.'],
                ['q' => 'Are files uploaded to a server?', 'a' => 'No. Files are read and encoded right in your browser.'],
            ],
        ],
        [
            'slug' => 'url-encoder',
            'name' => 'URL Encoder',
            'tagline' => 'Percent-encode & decode URL components.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'link'],
            'meta' => [
                'title' => 'URL Encoder & Decoder (Percent-encode Online)',
                'description' => 'Percent-encode and decode URLs and URL components in the browser. No sign-up.',
            ],
            'howto' => [
                'Paste a URL or component.',
                'Pick component or full-URL encoding.',
                'Copy the encoded or decoded output.',
            ],
            'faqs' => [
                ['q' => 'What\'s the difference between component and full-URL encoding?', 'a' => 'Component encoding (encodeURIComponent) escapes characters like ? and &. Full-URL encoding (encodeURI) leaves URL-structure characters unescaped.'],
            ],
        ],
        [
            'slug' => 'jwt-decoder',
            'name' => 'JWT Decoder',
            'tagline' => 'Inspect a JWT: header, payload, expiry.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'key'],
            'meta' => [
                'title' => 'JWT Decoder: Inspect Header, Payload, Expiry',
                'description' => 'Decode JSON Web Tokens to see the header, payload, and expiry. Tokens stay in your browser.',
            ],
            'howto' => [
                'Paste a JWT.',
                'See the decoded header and payload, plus expiry status.',
            ],
            'faqs' => [
                ['q' => 'Does this verify the signature?', 'a' => 'No, it only decodes the token. Verifying the signature needs the issuer\'s key.'],
                ['q' => 'Are tokens uploaded?', 'a' => 'No. Decoding happens locally, so it\'s safe to paste sensitive tokens.'],
            ],
        ],
        [
            'slug' => 'hash-generator',
            'name' => 'Hash Generator',
            'tagline' => 'MD5, SHA-1, SHA-256 & SHA-512 for text or file.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'finger-print'],
            'meta' => [
                'title' => 'Hash Generator: MD5, SHA-1, SHA-256, SHA-512',
                'description' => 'Hash text or files with MD5, SHA-1, SHA-256, and SHA-512. Runs locally, so nothing is uploaded.',
            ],
            'howto' => [
                'Pick text or file mode.',
                'Type or upload your input.',
                'Copy the hash for any algorithm shown.',
            ],
            'faqs' => [
                ['q' => 'Which algorithms are available?', 'a' => 'MD5, SHA-1, SHA-256, and SHA-512. MD5 and SHA-1 are no longer safe for cryptographic use.'],
                ['q' => 'Are files uploaded?', 'a' => 'No. Files are read locally with the Web Crypto API.'],
            ],
        ],
        [
            'slug' => 'color-converter',
            'name' => 'Color Converter',
            'tagline' => 'HEX ↔ RGB ↔ HSL with a contrast checker.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'swatch'],
            'meta' => [
                'title' => 'Color Converter (HEX ↔ RGB ↔ HSL with Contrast)',
                'description' => 'Convert colors between HEX, RGB, and HSL, and check WCAG contrast ratios between two colors.',
            ],
            'howto' => [
                'Enter a color in any format: HEX, RGB, or HSL.',
                'The equivalents in the other formats appear alongside.',
                'Pair two colors to see the WCAG contrast ratio.',
            ],
            'faqs' => [
                ['q' => 'How is contrast measured?', 'a' => 'Using the WCAG 2.1 luminance ratio formula. Pass/fail thresholds for AA and AAA at normal and large text are shown.'],
            ],
        ],
        [
            'slug' => 'json-formatter',
            'name' => 'JSON Formatter',
            'tagline' => 'Pretty-print, minify, and validate JSON.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'code-bracket'],
            'meta' => [
                'title' => 'JSON Formatter: Pretty-print, Minify, Validate',
                'description' => 'Format, prettify, minify, and validate JSON. Pasted JSON stays in your browser, no sign-up.',
            ],
            'howto' => [
                'Paste JSON.',
                'Pick pretty-print or minify.',
                'Errors point at the offending line and column.',
            ],
            'faqs' => [
                ['q' => 'Are comments or trailing commas allowed?', 'a' => 'Strict JSON allows neither. The parser reports the location of the first issue it hits.'],
                ['q' => 'Is my JSON sent to a server?', 'a' => 'No. Parsing and formatting happen in your browser.'],
            ],
        ],
        [
            'slug' => 'format-converter',
            'name' => 'Format Converter',
            'tagline' => 'Convert between JSON, YAML, CSV & XML.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'arrow-path-rounded-square'],
            'meta' => [
                'title' => 'Format Converter: JSON, YAML, CSV, XML',
                'description' => 'Convert data between JSON, YAML, CSV, and XML. Pick a source and target. No sign-up, no upload.',
            ],
            'howto' => [
                'Pick the source format your data is in (JSON, YAML, CSV, or XML).',
                'Pick the target format to convert to.',
                'Paste your data. The converted output appears on the right.',
                'Adjust per-format options as needed: CSV delimiter, XML root element, indent.',
                'Use the swap button to flip the conversion direction without retyping.',
            ],
            'faqs' => [
                ['q' => 'Is my data uploaded anywhere?', 'a' => 'No. The conversion runs in your browser, so nothing is sent to a server.'],
                ['q' => 'How does CSV ↔ JSON work?', 'a' => 'A CSV with a header row becomes an array of objects keyed by column. Going the other way, an array of objects becomes rows, and a single object becomes a one-row CSV.'],
                ['q' => 'How does JSON → XML choose tag names?', 'a' => 'Object keys become element names. The whole document is wrapped in a single root element; you can rename it with the Root field.'],
                ['q' => 'Are XML attributes preserved?', 'a' => 'Yes. Attributes appear in the parsed object with an "@_" prefix and round-trip back to attributes when serialising to XML.'],
                ['q' => 'What about YAML anchors and tags?', 'a' => 'Standard YAML 1.2 features parse correctly. Custom tags and complex anchors may not survive a round trip through other formats.'],
            ],
        ],
        [
            'slug' => 'regex-tester',
            'name' => 'Regex Tester',
            'tagline' => 'Build and debug regular expressions.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'magnifying-glass'],
            'meta' => [
                'title' => 'Regex Tester: Build & Debug Regular Expressions',
                'description' => 'Test JavaScript regular expressions against sample text. Matches and capture groups highlight as you type.',
            ],
            'howto' => [
                'Type a regex and pick flags (g, i, m, s, u, y).',
                'Paste sample text below.',
                'Matches and capture groups highlight live as you type.',
            ],
            'faqs' => [
                ['q' => 'Which regex flavor is supported?', 'a' => 'JavaScript regex (ECMAScript). Lookbehinds and Unicode property escapes work in modern browsers.'],
            ],
        ],
        [
            'slug' => 'browser-info',
            'name' => 'Browser Info',
            'tagline' => 'Viewport, screen size, DPR, browser & OS.',
            'category' => 'data',
            'icon' => ['type' => 'flux', 'name' => 'computer-desktop'],
            'meta' => [
                'title' => 'Browser Info: Viewport, Screen, DPR, Device',
                'description' => 'Shows your viewport size, screen resolution, device pixel ratio, browser, and OS. Handy when debugging responsive layouts.',
            ],
            'howto' => [
                'Open this page on the device you want to inspect.',
                'Resize the window to watch the viewport update live.',
            ],
            'faqs' => [
                ['q' => 'What\'s the difference between viewport and screen size?', 'a' => 'Viewport is the visible area of the page. Screen size is the full physical display, including space outside the browser window.'],
            ],
        ],
        [
            'slug' => 'password-generator',
            'name' => 'Password Generator',
            'tagline' => 'Strong, random passwords with a strength meter.',
            'category' => 'generators',
            'icon' => ['type' => 'flux', 'name' => 'lock-closed'],
            'meta' => [
                'title' => 'Password Generator: Strong & Random',
                'description' => 'Generates strong, random passwords with a built-in strength meter. No sign-up, runs in your browser.',
            ],
            'howto' => [
                'Pick a length and the character classes to include.',
                'Click generate to roll a new password.',
                'Copy with one click.',
            ],
            'faqs' => [
                ['q' => 'How are passwords generated?', 'a' => 'Using the browser\'s cryptographically secure random source (window.crypto). Nothing leaves your device.'],
                ['q' => 'How is strength estimated?', 'a' => 'With zxcvbn-style heuristics that look at length, character classes, and common patterns.'],
            ],
        ],
        [
            'slug' => 'passphrase-generator',
            'name' => 'Passphrase Generator',
            'tagline' => 'Memorable, random word-based passphrases.',
            'category' => 'generators',
            'icon' => ['type' => 'flux', 'name' => 'key'],
            'meta' => [
                'title' => 'Passphrase Generator: Random, Memorable',
                'description' => 'Generate Diceware-style passphrases from the EFF large wordlist. Easier to remember than passwords, and just as strong with enough words.',
            ],
            'howto' => [
                'Pick how many words to use and a separator.',
                'Optionally append a digit or symbol for sites that require them.',
                'Hit Regenerate for a new passphrase, then copy.',
            ],
            'faqs' => [
                ['q' => 'Where do the words come from?', 'a' => 'The EFF large wordlist (7,776 words). Each word is picked with the browser\'s cryptographic random source.'],
                ['q' => 'How many words do I need?', 'a' => 'Three words give roughly 38 bits of entropy. Five words gets you past 64 bits, which resists offline cracking. Six or seven is comfortable for high-value accounts.'],
                ['q' => 'Is the passphrase generated on a server?', 'a' => 'No. Word selection runs locally using window.crypto, so nothing leaves your device.'],
            ],
        ],
        [
            'slug' => 'uuid-generator',
            'name' => 'UUID Generator',
            'tagline' => 'Generate v4 or v7 UUIDs in bulk.',
            'category' => 'generators',
            'icon' => ['type' => 'flux', 'name' => 'identification'],
            'meta' => [
                'title' => 'UUID Generator: v4 and v7 in Bulk',
                'description' => 'Generate UUID v4 (random) or v7 (timestamp-ordered) identifiers in bulk, then copy or download the list.',
            ],
            'howto' => [
                'Pick UUID v4 or v7.',
                'Choose how many to generate.',
                'Copy or download the list.',
            ],
            'faqs' => [
                ['q' => 'What\'s the difference between v4 and v7?', 'a' => 'v4 is fully random. v7 prepends a millisecond timestamp so IDs sort chronologically, which is friendlier to database indexes.'],
            ],
        ],
        [
            'slug' => 'image-resizer',
            'name' => 'Image Resizer',
            'tagline' => 'Resize, convert & build favicons in your browser.',
            'category' => 'generators',
            'icon' => ['type' => 'flux', 'name' => 'photo'],
            'meta' => [
                'title' => 'Image Resizer: Resize, Convert, Build Favicons',
                'description' => 'Resize images, convert between PNG, JPEG, and WebP, and build favicons. Nothing is uploaded; files stay on your device.',
            ],
            'howto' => [
                'Drop in or pick an image.',
                'Set the target dimensions, position, and rotation.',
                'Pick an output format and download.',
            ],
            'faqs' => [
                ['q' => 'Is my image uploaded?', 'a' => 'No. Resizing and conversion run on a canvas in your browser, so files never leave your device.'],
                ['q' => 'Which output formats are supported?', 'a' => 'PNG, JPEG, and WebP. Multi-size .ico is generated for favicons.'],
            ],
        ],
        [
            'slug' => 'slug-generator',
            'name' => 'Slug Generator',
            'tagline' => 'Turn any string into a URL slug.',
            'category' => 'generators',
            'icon' => ['type' => 'flux', 'name' => 'link'],
            'meta' => [
                'title' => 'Slug Generator: String to URL Slug',
                'description' => 'Turn any string into a URL-friendly slug. Handles diacritics, emoji, and custom separators.',
            ],
            'howto' => [
                'Type or paste the string you want to slugify.',
                'Pick a separator and case style.',
                'Copy the result.',
            ],
            'faqs' => [
                ['q' => 'How are diacritics handled?', 'a' => 'Diacritics (é, ü, ñ, etc.) are normalized to their ASCII equivalents.'],
                ['q' => 'What about emoji?', 'a' => 'Emoji are stripped, so slugs end up as plain ASCII suitable for URLs.'],
            ],
        ],
        [
            'slug' => 'lorem-ipsum',
            'name' => 'Lorem Ipsum',
            'tagline' => 'Generate placeholder text.',
            'category' => 'generators',
            'icon' => ['type' => 'flux', 'name' => 'document-text'],
            'meta' => [
                'title' => 'Lorem Ipsum Generator: Placeholder Text',
                'description' => 'Generate Lorem Ipsum placeholder text by paragraphs, sentences, or words.',
            ],
            'howto' => [
                'Pick paragraphs, sentences, or words.',
                'Choose how much you need.',
                'Copy with one click.',
            ],
            'faqs' => [
                ['q' => 'Why use Lorem Ipsum?', 'a' => 'It gives you the visual weight of real prose without pulling reviewers into reading the placeholder copy.'],
            ],
        ],
    ],

];
