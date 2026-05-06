<?php

test('the dashboard renders', function () {
    $this->get('/')->assertOk();
});

test('the barcode generator renders', function () {
    $this->get('/barcode-generator')->assertOk();
});

test('the percentage calculator renders', function () {
    $this->get('/percentage-calculator')->assertOk();
});

test('the character counter renders', function () {
    $this->get('/character-counter')->assertOk();
});

test('the markdown converter renders', function () {
    $this->get('/markdown-converter')->assertOk();
});
