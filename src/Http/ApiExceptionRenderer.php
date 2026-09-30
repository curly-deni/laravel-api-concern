<?php

namespace Aesis\ApiConcern\Http;

use Aesis\ApiConcern\Exceptions\ApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionRenderer
{
    public function __construct(private readonly ApiResponseFactory $responses) {}

    public function render(Throwable $exception): Response
    {
        if ($exception instanceof ApiException) {
            return $this->responses->error(
                $exception->errorCode,
                $exception->getMessage(),
                $exception->status,
                $exception->details,
            );
        }

        if ($exception instanceof ValidationException) {
            return $this->responses->error(
                'validation_failed',
                $this->message('validation_failed'),
                $exception->status,
                ['fields' => $exception->errors()],
            );
        }

        if ($exception instanceof AuthenticationException) {
            return $this->responses->error('unauthenticated', $this->message('unauthenticated'), 401);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->responses->error('forbidden', $this->message('forbidden'), 403);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->responses->error('not_found', $this->message('not_found'), 404);
        }

        if ($exception instanceof ThrottleRequestsException) {
            return $this->responses->error(
                'too_many_requests',
                $this->message('too_many_requests'),
                429,
                [],
                array_filter([
                    'Retry-After' => $exception->getHeaders()['Retry-After'] ?? null,
                ]),
            );
        }

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();

            return $this->responses->error(
                $this->codeForStatus($status),
                $status >= 500 ? $this->message('server_error') : ($exception->getMessage() ?: $this->message('request_failed')),
                $status,
                [],
                $exception->getHeaders(),
            );
        }

        report($exception);

        return $this->responses->error('server_error', $this->message('server_error'), 500);
    }

    private function message(string $key): string
    {
        return (string) __("api-concern::messages.{$key}");
    }

    private function codeForStatus(int $status): string
    {
        return match ($status) {
            400 => 'bad_request',
            401 => 'unauthenticated',
            403 => 'forbidden',
            404 => 'not_found',
            405 => 'method_not_allowed',
            408 => 'request_timeout',
            409 => 'conflict',
            413 => 'payload_too_large',
            415 => 'unsupported_media_type',
            422 => 'unprocessable_entity',
            429 => 'too_many_requests',
            default => $status >= 500 ? 'server_error' : 'request_failed',
        };
    }
}
