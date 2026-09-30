<?php

use Aesis\ApiConcern\Exceptions\ApiException;
use Aesis\ApiConcern\Http\ApiExceptionRenderer;
use Aesis\ApiConcern\Tests\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(TestCase::class);

it('renders application API exceptions', function () {
    $response = app(ApiExceptionRenderer::class)->render(
        new ApiException('account_locked', 'Account is locked.', 423, ['retry_after' => 60]),
    );

    expect($response->getStatusCode())->toBe(423)
        ->and(json_decode($response->getContent(), true))
        ->toBe(['error' => [
            'code' => 'account_locked',
            'message' => 'Account is locked.',
            'details' => ['retry_after' => 60],
        ]]);
});

it('renders validation errors with the English translation', function () {
    $exception = ValidationException::withMessages(['email' => ['Invalid email.']]);
    $response = app(ApiExceptionRenderer::class)->render($exception);

    expect($response->getStatusCode())->toBe(422)
        ->and(json_decode($response->getContent(), true))
        ->toBe(['error' => [
            'code' => 'validation_failed',
            'message' => 'The submitted data is invalid.',
            'details' => ['fields' => ['email' => ['Invalid email.']]],
        ]]);
});

it('maps authentication and HTTP exceptions to API errors', function () {
    $renderer = app(ApiExceptionRenderer::class);
    $authResponse = $renderer->render(new AuthenticationException);
    $httpResponse = $renderer->render(new HttpException(404, 'Gone'));

    expect($authResponse->getStatusCode())->toBe(401)
        ->and(json_decode($authResponse->getContent(), true)['error']['code'])->toBe('unauthenticated')
        ->and($httpResponse->getStatusCode())->toBe(404)
        ->and(json_decode($httpResponse->getContent(), true)['error']['code'])->toBe('not_found');
});

it('maps authorization, missing model, and throttling exceptions', function () {
    $renderer = app(ApiExceptionRenderer::class);
    $forbidden = $renderer->render(new AuthorizationException);
    $notFound = $renderer->render(new ModelNotFoundException);
    $throttled = $renderer->render(new ThrottleRequestsException(headers: ['Retry-After' => '30']));

    expect($forbidden->getStatusCode())->toBe(403)
        ->and(json_decode($forbidden->getContent(), true)['error']['code'])->toBe('forbidden')
        ->and($notFound->getStatusCode())->toBe(404)
        ->and(json_decode($notFound->getContent(), true)['error']['code'])->toBe('not_found')
        ->and($throttled->getStatusCode())->toBe(429)
        ->and($throttled->headers->get('Retry-After'))->toBe('30');
});

it('translates messages using the active application locale', function () {
    app()->setLocale('ru');

    $response = app(ApiExceptionRenderer::class)->render(
        ValidationException::withMessages(['email' => ['Неверный адрес.']]),
    );

    expect($response->getStatusCode())->toBe(422)
        ->and(json_decode($response->getContent(), true)['error']['message'])
        ->toBe('Проверьте корректность введённых данных.');
});
