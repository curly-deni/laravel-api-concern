<?php

namespace Aesis\ApiConcern\Concerns;

use Aesis\ApiConcern\Http\ApiResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

trait HasApiResponses
{
    /**
     * @param  array<string, mixed>  $meta
     */
    protected function data(mixed $data, int $status = 200, array $meta = []): JsonResponse
    {
        return app(ApiResponseFactory::class)->data($data, $status, $meta);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function created(mixed $data, array $meta = []): JsonResponse
    {
        return app(ApiResponseFactory::class)->created($data, $meta);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function accepted(mixed $data, array $meta = []): JsonResponse
    {
        return app(ApiResponseFactory::class)->accepted($data, $meta);
    }

    protected function noContent(): Response
    {
        return app(ApiResponseFactory::class)->noContent();
    }

    protected function resource(JsonResource $resource, ?int $status = null): JsonResponse
    {
        return app(ApiResponseFactory::class)->resource($resource, $status);
    }

    protected function createdResource(JsonResource $resource): JsonResponse
    {
        return $this->resource($resource, 201);
    }

    protected function acceptedResource(JsonResource $resource): JsonResponse
    {
        return $this->resource($resource, 202);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    protected function error(string $code, string $message, int $status, array $details = []): JsonResponse
    {
        return app(ApiResponseFactory::class)->error($code, $message, $status, $details);
    }
}
