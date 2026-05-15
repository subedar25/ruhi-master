<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Safe return targets after the organization picker (avoid 404/405 from stale or invalid URLs).
 */
final class OrganizationReturnUrl
{
    private const SESSION_KEY = 'organization_select_return_url';

    /** @var list<string> */
    private const EXCLUDED_PATH_PREFIXES = [
        '/master-app/organization/select',
        '/master-app/organization/switch',
    ];

    public static function captureForPicker(Request $request): void
    {
        if (! $request->isMethod('GET')) {
            return;
        }

        $candidate = session('url.intended');
        if (! is_string($candidate) || $candidate === '') {
            $candidate = $request->getRequestUri();
        }

        $path = self::normalizeToPath($candidate);
        if ($path === null || ! self::isAllowedReturnPath($path)) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $path);
    }

    public static function pullAllowedPath(Request $request): ?string
    {
        $stored = $request->session()->pull(self::SESSION_KEY);
        if (! is_string($stored) || $stored === '') {
            return null;
        }

        $path = self::normalizeToPath($stored);
        if ($path === null || ! self::isAllowedReturnPath($path)) {
            return null;
        }

        return $path;
    }

    public static function dashboardPath(): string
    {
        return route('masterapp.dashboard', [], false);
    }

    private static function normalizeToPath(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '//')) {
            return null;
        }

        if (str_starts_with($value, '/')) {
            $path = parse_url($value, PHP_URL_PATH);

            return is_string($path) && $path !== '' ? $path : null;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '' && str_starts_with($value, $appUrl)) {
            $path = parse_url($value, PHP_URL_PATH);

            return is_string($path) && $path !== '' ? $path : null;
        }

        return null;
    }

    private static function isAllowedReturnPath(string $path): bool
    {
        foreach (self::EXCLUDED_PATH_PREFIXES as $excluded) {
            if ($path === $excluded || str_starts_with($path, $excluded.'?')) {
                return false;
            }
        }

        if ($path === '/dashboard') {
            return true;
        }

        return str_starts_with($path, '/master-app/');
    }
}
