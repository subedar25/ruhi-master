<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

/**
 * Resolves the current organization for the session using, in order:
 * 1. Session {@see session()} key {@code current_organization_id} when it is still allowed for the user.
 * 2. {@see User::$last_selected_organization_id} when present and allowed (then written to the session).
 * 3. If the user has exactly one organization (via {@code organization_user} for normal users, or all orgs for system users), auto-assign.
 * 4. If the user has more than one organization and none of the above applies, the session key is cleared so the UI can prompt for a choice.
 */
final class OrganizationSessionResolver
{
    /**
     * @return EloquentCollection<int, Organization>
     */
    public static function allowedOrganizations(User $user): EloquentCollection
    {
        $isSystemUser = ($user->user_type ?? '') === 'systemuser';
        if ($isSystemUser) {
            /** @var EloquentCollection<int, Organization> $orgs */
            $orgs = Organization::query()->orderBy('name')->get(['id', 'name', 'logo']);

            return $orgs;
        }

        /** @var EloquentCollection<int, Organization> $orgs */
        $orgs = $user->organizations()->orderBy('name')->get(['organizations.id', 'organizations.name', 'organizations.logo']);

        return $orgs;
    }

    /**
     * @return list<int>
     */
    public static function allowedOrganizationIds(User $user): array
    {
        return self::allowedOrganizations($user)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    }

    public static function sync(Request $request): void
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return;
        }

        // After POST /organization/switch the DB row is updated before this GET runs; reload so
        // last_selected_organization_id is not stale on the in-memory model (avoids multi-org sync
        // clearing session before we can hydrate from the user row).
        if ((int) $request->session()->get('current_organization_id', 0) === 0) {
            $user->refresh();
        }

        $organizationIds = self::allowedOrganizationIds($user);
        $request->session()->put('user_organization_ids', $organizationIds);

        $sessionOrgId = (int) $request->session()->get('current_organization_id', 0);
        if ($sessionOrgId > 0 && in_array($sessionOrgId, $organizationIds, true)) {
            return;
        }

        if ($sessionOrgId > 0) {
            $request->session()->forget('current_organization_id');
        }

        $fromUser = (int) ($user->last_selected_organization_id ?? 0);
        if ($fromUser > 0 && ! in_array($fromUser, $organizationIds, true)) {
            $user->persistLastSelectedOrganizationId(null);
            $fromUser = 0;
        }

        if ($fromUser > 0 && in_array($fromUser, $organizationIds, true)) {
            $request->session()->put('current_organization_id', $fromUser);

            return;
        }

        if ($organizationIds === []) {
            $request->session()->forget('current_organization_id');

            return;
        }

        if (count($organizationIds) === 1) {
            $onlyId = (int) $organizationIds[0];
            $request->session()->put('current_organization_id', $onlyId);
            if ((int) ($user->last_selected_organization_id ?? 0) !== $onlyId) {
                $user->persistLastSelectedOrganizationId($onlyId);
            }

            return;
        }

        $request->session()->forget('current_organization_id');
    }
}
