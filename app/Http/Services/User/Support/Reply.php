<?php

namespace App\Http\Services\User\Support;

use App\Http\Core\Exceptions\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Reply
{
    public static function fromException(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return self::fail('validation_failed', 'The given data was invalid.', 422, $e->errors());
        }

        if ($e instanceof DomainException) {
            return self::fail($e->errorCode, $e->getMessage(), $e->status);
        }

        if ($e instanceof AuthenticationException) {
            return self::fail('unauthenticated', 'Unauthenticated.', 401);
        }

        if ($e instanceof AuthorizationException) {
            return self::fail('forbidden', 'This action is unauthorized.', 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return self::fail('not_found', 'Resource not found.', 404);
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            return self::fail(self::codeForStatus($status), $e->getMessage() !== '' ? $e->getMessage() : 'Request failed.', $status);
        }

        report($e);

        return self::fail('server_error', 'Something went wrong.', 500);
    }

    private static function codeForStatus(int $status): string
    {
        return match ($status) {
            401 => 'unauthenticated',
            403 => 'forbidden',
            404 => 'not_found',
            405 => 'method_not_allowed',
            429 => 'too_many_requests',
            default => 'request_failed',
        };
    }

    public static function ok($data = null, int $statusCode = 200, ?array $meta = null, string $message = 'OK'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
            'error' => null,
            'meta' => $meta,
            'locale' => app()->getLocale() ?: 'en',
        ], $statusCode);
    }

    public static function fail(string $code, string $message, int $statusCode = 400, ?array $details = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => null,
            'error' => array_filter([
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ], fn ($v) => $v !== null),
            'meta' => null,
            'locale' => app()->getLocale() ?: 'en',
        ], $statusCode);
    }
}
