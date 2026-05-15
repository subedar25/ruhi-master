<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\OrganizationReturnUrl;
use App\Support\OrganizationSessionResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * When an authenticated user belongs to more than one organization and no valid
 * organization is in the session yet, redirect to the picker until they choose one.
 */
final class EnforceOrganizationSelection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if ($request->is('livewire/*')) {
            return $next($request);
        }

        if ($request->routeIs('masterapp.organization.select', 'masterapp.organization.switch', 'logout')) {
            return $next($request);
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return $next($request);
        }

        if ((int) $request->session()->get('current_organization_id', 0) > 0) {
            return $next($request);
        }

        $orgs = OrganizationSessionResolver::allowedOrganizations($user);
        if ($orgs->count() <= 1) {
            return $next($request);
        }

        OrganizationReturnUrl::captureForPicker($request);

        return redirect()->route('masterapp.organization.select');
    }
}
