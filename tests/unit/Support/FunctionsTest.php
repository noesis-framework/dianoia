<?php

declare(strict_types=1);

test('json()', function () {
    $response = json('success');

    $expect = json_encode(
        ['data' => 'success'],
        JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );

    expect($response->getBody()->getContents())->toBe($expect);
    expect($response->getHeader('Content-Type'))->toBeArray();
    expect($response->getHeader('Content-Type'))->toContain('application/hal+json');
});

test('response() returns json from array', function () {
    $response = response(['test' => 'success']);

    $expect = json_encode(
        ['data' => ['test' => 'success']]
    );

    expect($response->getBody()->getContents())->toBe($expect);
    expect($response->getHeader('Content-Type'))->toBeArray();
    expect($response->getHeader('Content-Type'))->toContain('application/json');
});

test('response() returns HtmlResponse when passed a string', function () {
    $response = response('success');

    $expect = 'success';

    expect($response->getBody()->getContents())->toBe($expect);
    expect($response->getHeader('Content-Type'))->toBeArray();
    expect($response->getHeader('Content-Type'))->toContain('text/html; charset=utf-8');
});

test('response() returns JsonResponse when passed an object', function () {
    $object = new \stdClass;
    $object->test = 'success';
    $response = response($object);

    $expected = json_encode(['data' => ['test' => 'success']]);

    expect($response->getBody()->getContents())->toBe($expected);
});

test('redirect() returns redirect response', function () {
    $response = redirect('/', 200);

    expect($response->getHeader('Location'))->toContain('/');
    expect($response->getStatusCode())->toBe(200);
});
