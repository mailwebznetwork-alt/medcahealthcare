<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function __construct(private readonly ActivityLogService $activityLogService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            $this->activityLogService->log(
                'unauthorized_access_attempt',
                'integrations',
                'Unauthenticated request blocked by admin middleware.'
            );

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => [],
            ], 401);
        }

        $role = trim((string) ($user->role ?? ''));
        $allowedRoles = ['admin', 'super_admin'];

        if (! in_array($role, $allowedRoles, true)) {
            $this->activityLogService->log(
                'role_violation',
                'integrations',
                sprintf('User %d blocked by admin middleware.', (int) $user->id)
            );

            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'data' => [],
            ], 403);
        }

        return $next($request);
    }
}
