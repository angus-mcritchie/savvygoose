import js from '@eslint/js';
import globals from 'globals';

export default [
    {
        ignores: ['public/**', 'vendor/**', 'node_modules/**', 'bootstrap/**'],
    },
    js.configs.recommended,
    {
        files: ['resources/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 2023,
            sourceType: 'module',
            globals: {
                ...globals.browser,
                Alpine: 'readonly',
            },
        },
        rules: {
            // Real correctness signals stay as errors (no-undef, no-dupe-keys,
            // no-unreachable, ...). Style-ish noise is downgraded so the lint
            // gate flags bugs, not bikeshedding.
            'no-unused-vars': 'warn',
            'no-empty': 'off',
            'no-constant-condition': ['warn', { checkLoops: false }],
        },
    },
];
