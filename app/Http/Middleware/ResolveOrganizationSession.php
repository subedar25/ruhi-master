<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\OrganizationSessionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveOrganizationSession
{
    public function handle(Request $request, Closure $next): Response
    {
        OrganizationSessionResolver::sync($request);

        return $next($request);
    }
}
