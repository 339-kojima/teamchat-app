<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/v1', 'api/v1/*')) {
                return null;
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'error' => [
                        'code' => 'not_found',
                        'message' => 'チャンネルが見つかりません。',
                    ],
                ], 404);
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'error' => [
                        'code' => 'method_not_allowed',
                        'message' => 'このAPIは読み取り専用です。',
                    ],
                ], 405);
            }

            return response()->json([
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'サーバーエラーが発生しました。',
                ],
            ], 500);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
