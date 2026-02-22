<?php

namespace Modules\User\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            $this->writePermissionLog('unauthenticated_request', $request, null, null, ['message' => 'Unauthenticated']);
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $routeName = $request->route()?->getName();
        if (empty($routeName)) {
            return $next($request);
        }

        $permissions = $user->per ?? [];
        if (!is_array($permissions)) {
            $permissions = [];
        }

        if (in_array('*', $permissions, true)) {
            return $next($request);
        }

        if (!$this->hasPermission($permissions, $routeName)) {
            $this->writePermissionLog('permission_denied', $request, $user->id, $routeName, [
                'user_permissions' => $permissions,
            ]);
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }

    protected function hasPermission(array $permissions, string $routeName): bool
    {
        if (in_array($routeName, $permissions, true)) {
            return true;
        }

        $parts = explode('.', $routeName);
        if (count($parts) < 2) {
            return false;
        }

        $action = array_pop($parts);
        $scope = implode('.', $parts);
        $mappedAction = $this->mapAction($action);

        $candidates = [
            "{$scope}.{$mappedAction}",
            "{$scope}.*",
        ];

        foreach ($candidates as $candidate) {
            if (in_array($candidate, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    protected function mapAction(string $routeAction): string
    {
        return match ($routeAction) {
            'store' => 'create',
            'update', 'restore' => 'edit',
            'destroy', 'forceDestroy', 'force_destroy' => 'delete',
            default => 'view',
        };
    }

    protected function writePermissionLog(
        string $type,
        Request $request,
        ?int $userId,
        ?string $routeName,
        array $context = []
    ): void {
        $logDir = storage_path('log_monitor');
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'permissions.log';
        if (!File::exists($logDir)) {
            File::makeDirectory($logDir, 0755, true);
        }

        $payload = array_merge([
            'time' => now()->toDateTimeString(),
            'type' => $type,
            'user_id' => $userId,
            'method' => $request->method(),
            'path' => $request->path(),
            'route_name' => $routeName,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], $context);

        File::append($logFile, json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }
}
