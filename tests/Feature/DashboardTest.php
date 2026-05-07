<?php

test('the dashboard renders', function () {
    $this->get('/')->assertOk();
});

test('category index pages render', function (string $category) {
    $this->get('/'.$category)->assertOk();
})->with(['text', 'data', 'numbers', 'generators']);

test('the barcode generator renders', function () {
    $this->get('/barcode-generator')->assertOk();
});

test('the percentage calculator renders', function () {
    $this->get('/percentage-calculator')->assertOk();
});

test('the unit converter renders', function () {
    $this->get('/unit-converter')->assertOk();
});

test('the timestamp converter renders', function () {
    $this->get('/timestamp-converter')->assertOk();
});

test('the color converter renders', function () {
    $this->get('/color-converter')->assertOk();
});

test('the character counter renders', function () {
    $this->get('/character-counter')->assertOk();
});

test('the markdown converter renders', function () {
    $this->get('/markdown-converter')->assertOk();
});

test('the qr code generator renders', function () {
    $this->get('/qr-code-generator')->assertOk();
});

test('the base64 encoder renders', function () {
    $this->get('/base64-encoder')->assertOk();
});

test('the url encoder renders', function () {
    $this->get('/url-encoder')->assertOk();
});

test('the jwt decoder renders', function () {
    $this->get('/jwt-decoder')->assertOk();
});

test('the hash generator renders', function () {
    $this->get('/hash-generator')->assertOk();
});

test('the password generator renders', function () {
    $this->get('/password-generator')->assertOk();
});

test('the uuid generator renders', function () {
    $this->get('/uuid-generator')->assertOk();
});

test('the image resizer renders', function () {
    $this->get('/image-resizer')->assertOk();
});

test('the json formatter renders', function () {
    $this->get('/json-formatter')->assertOk();
});

test('the diff viewer renders', function () {
    $this->get('/diff-viewer')->assertOk();
});

test('the case converter renders', function () {
    $this->get('/case-converter')->assertOk();
});

test('the slug generator renders', function () {
    $this->get('/slug-generator')->assertOk();
});

test('the lorem ipsum generator renders', function () {
    $this->get('/lorem-ipsum')->assertOk();
});

test('the regex tester renders', function () {
    $this->get('/regex-tester')->assertOk();
});

test('the browser info tool renders', function () {
    $this->get('/browser-info')->assertOk();
});

test('the time between dates tool renders', function () {
    $this->get('/time-between-dates')->assertOk();
});

test('the holidays api returns holidays for a country and range', function () {
    $response = $this->getJson('/api/holidays?country=us&from=2026-01-01&to=2026-12-31');

    $response->assertOk()
        ->assertJsonPath('country', 'us')
        ->assertJsonStructure(['country', 'from', 'to', 'holidays' => [['date', 'name', 'type']]]);

    expect($response->json('holidays'))->not->toBeEmpty();
});

test('the holidays api rejects an unsupported country', function () {
    $this->getJson('/api/holidays?country=zz&from=2026-01-01&to=2026-12-31')
        ->assertStatus(422);
});

test('the holidays api rejects an invalid date range', function () {
    $this->getJson('/api/holidays?country=us&from=2026-12-31&to=2026-01-01')
        ->assertStatus(422);
});
