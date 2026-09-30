<?php

use Aesis\ApiConcern\Concerns\HasApiResponses;
use Aesis\ApiConcern\Http\ApiResponseFactory;
use Aesis\ApiConcern\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

uses(TestCase::class);

it('wraps data and includes metadata only when provided', function () {
    $factory = app(ApiResponseFactory::class);

    expect($factory->data(['id' => 1])->getData(true))
        ->toBe(['data' => ['id' => 1]])
        ->and($factory->data(['id' => 1], 200, ['page' => 2])->getData(true))
        ->toBe(['data' => ['id' => 1], 'meta' => ['page' => 2]]);
});

it('returns created and accepted statuses', function () {
    $factory = app(ApiResponseFactory::class);

    expect($factory->created([])->getStatusCode())->toBe(201)
        ->and($factory->accepted([])->getStatusCode())->toBe(202);
});

it('returns an empty no content response', function () {
    $response = app(ApiResponseFactory::class)->noContent();

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(204)
        ->and($response->getContent())->toBe('');
});

it('preserves resource response data and allows a status override', function () {
    $resource = JsonResource::make(['id' => 1]);
    $response = app(ApiResponseFactory::class)->resource($resource, 201);

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(201)
        ->and($response->getData(true))->toBe(['data' => ['id' => 1]]);
});

it('formats errors with optional details', function () {
    $factory = app(ApiResponseFactory::class);

    expect($factory->error('invalid', 'Invalid input', 422, ['field' => 'name'])->getData(true))
        ->toBe(['error' => [
            'code' => 'invalid',
            'message' => 'Invalid input',
            'details' => ['field' => 'name'],
        ]])
        ->and($factory->error('missing', 'Missing', 404)->getData(true))
        ->toBe(['error' => ['code' => 'missing', 'message' => 'Missing']]);
});

it('makes response helpers available to controllers using the concern', function () {
    $controller = new class
    {
        use HasApiResponses;

        public function createdResponse(): JsonResponse
        {
            return $this->created(['id' => 1]);
        }
    };

    expect($controller->createdResponse()->getStatusCode())->toBe(201);
});
