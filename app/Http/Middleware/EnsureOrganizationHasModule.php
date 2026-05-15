<?php

namespace App\Http\Middleware;

use App\Support\OrganizationModule;
use App\Support\OrganizationSessionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationHasModule
{
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        OrganizationSessionResolver::sync($request);

        if (($request->user()?->user_type ?? '') === 'systemuser') {
            return $next($request);
        }

        $orgId = (int) session('current_organization_id', 0);
        if ($orgId === 0) {
            abort(403, 'Please select an organization.');
        }

        if (! OrganizationModule::currentOrganizationHasModule($moduleSlug)) {
            abort(403, 'This module is not enabled for your organization.');
        }

        return $next($request);
    }
}
