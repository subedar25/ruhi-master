<?php

namespace App\Support;

use App\Models\Organization;

/**
 * Resolves which application modules are enabled for the current session organization.
 */
final class OrganizationModule
{
    /**
     * @return list<string>|null null when no organization is selected (sidebar: show all; routes: use middleware)
     */
    public static function slugsForCurrentOrganization(): ?array
    {
        $orgId = (int) session('current_organization_id', 0);
        if ($orgId === 0) {
            return null;
        }

        static $cache = [];

        if (! isset($cache[$orgId])) {
            $cache[$orgId] = Organization::query()
                ->find($orgId)
                ?->modules()
                ->pluck('slug')
                ->all() ?? [];
        }

        return $cache[$orgId];
    }

    /**
     * Sidebar: when no org is selected, show links; when selected, filter by enabled modules.
     * System users always see every module.
     */
    public static function sidebarShow(string $moduleSlug): bool
    {
        if ((auth()->user()?->user_type ?? '') === 'systemuser') {
            return true;
        }

        $slugs = self::slugsForCurrentOrganization();
        if ($slugs === null) {
            return true;
        }

        return in_array($moduleSlug, $slugs, true);
    }

    /**
     * Route / API checks: organization must be selected and module enabled.
     * System users always pass.
     */
    public static function currentOrganizationHasModule(string $moduleSlug): bool
    {
        if ((auth()->user()?->user_type ?? '') === 'systemuser') {
            return true;
        }

        $slugs = self::slugsForCurrentOrganization();

        return $slugs !== null && in_array($moduleSlug, $slugs, true);
    }
}
