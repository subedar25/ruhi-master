<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Support\OrganizationReturnUrl;
use App\Support\OrganizationSessionResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrganizationContextController extends Controller
{
    public function select(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login.view');
        }

        $organizations = OrganizationSessionResolver::allowedOrganizations($user);

        if ($organizations->count() <= 1) {
            return redirect()->route('masterapp.dashboard');
        }

        $currentId = (int) session('current_organization_id', 0);
        $allowedIds = $organizations->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($currentId > 0 && in_array($currentId, $allowedIds, true)) {
            return redirect()->route('masterapp.dashboard');
        }

        OrganizationReturnUrl::captureForPicker($request);

        return view('masterapp.organization.select', [
            'organizations' => $organizations,
        ]);
    }

    public function switch(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
        ]);

        $user = $request->user();
        $organizationId = (int) $validated['organization_id'];

        $isSystemUser = ($user->user_type ?? '') === 'systemuser';

        $allowed = $isSystemUser
            ? Organization::whereKey($organizationId)->exists()
            : $user->organizations()->whereKey($organizationId)->exists();

        if (! $allowed) {
            abort(403, 'You do not have access to this organization.');
        }

        $request->session()->put('current_organization_id', $organizationId);
        $request->session()->put(
            'user_organization_ids',
            $isSystemUser
                ? Organization::orderBy('name')->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
                : $user->organizations()->orderBy('name')->pluck('organizations.id')->map(fn ($id) => (int) $id)->values()->all(),
        );

        $user->persistLastSelectedOrganizationId($organizationId);

        Auth::setUser($user->fresh());

        $request->session()->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Organization switched.',
                'organization_id' => $organizationId,
            ]);
        }

        return $this->redirectAfterOrganizationSwitch($request);
    }

    private function redirectAfterOrganizationSwitch(Request $request): RedirectResponse
    {
        $headers = ['Cache-Control' => 'no-store, no-cache, must-revalidate'];

        $returnPath = OrganizationReturnUrl::pullAllowedPath($request);
        if ($returnPath !== null) {
            return redirect()->to($returnPath, 303)->withHeaders($headers);
        }

        return redirect()->to(OrganizationReturnUrl::dashboardPath(), 303)->withHeaders($headers);
    }
}

