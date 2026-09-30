<?php

namespace Aesis\ApiConcern\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

final class ApiResponseFactory
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function data(mixed $data, int $status = 200, array $meta = []): JsonResponse
    {
        $payload = ['data' => $data];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function created(mixed $data, array $meta = []): JsonResponse
    {
        return $this->data($data, 201, $meta);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function accepted(mixed $data, array $meta = []): JsonResponse
    {
        return $this->data($data, 202, $meta);
    }

    public function noContent(): Response
    {
        return response()->noContent();
    }

    public function resource(JsonResource $resource, ?int $status = null): JsonResponse
    {
        $response = $resource->response(request());

        return response()->json(
            $response->getData(true),
            $status ?? $response->getStatusCode(),
            $response->headers->all(),
        );
    }

    /**
     * @param  array<string, mixed>  $details
     * @param  array<string, string>  $headers
     */
    public function error(
        string $code,
        string $message,
        int $status,
        array $details = [],
        array $headers = [],
    ): JsonResponse {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($details !== []) {
            $error['details'] = $details;
        }

        return response()->json(['error' => $error], $status, $headers);
    }
}
