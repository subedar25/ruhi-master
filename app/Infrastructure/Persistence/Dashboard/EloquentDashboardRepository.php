<?php

namespace App\Infrastructure\Persistence\Dashboard;

use App\Core\Dashboard\Contracts\DashboardRepository;
use App\Models\Invoice;
use App\Models\Outlet;
use App\Models\RuhiDesign;
use App\Models\RuhiDesignCategory;
use App\Models\RuhiGs;
use App\Models\RuhiItemType;
use App\Models\RuhiKstone;
use App\Models\RuhiProduct;
use App\Models\User;
use App\Models\Vendor;
use App\Support\InvoiceDepartmentAuthorization;
use Illuminate\Database\Eloquent\Builder;

class EloquentDashboardRepository implements DashboardRepository
{
    /**
     * @return array<string, int>
     */
    public function getCounts(?User $authUser): array
    {
        if (! $authUser) {
            return [
                'operational_general_manager' => 0,
                'general_manager' => 0,
                'area_manager' => 0,
                'accountant' => 0,
                'outlets' => 0,
                'vendors' => 0,
                'total_invoice' => 0,
                'completed_invoice' => 0,
                'pending_invoice' => 0,
                'in_process_invoice' => 0,
                'approved_invoice' => 0,
                'ruhi_gs' => 0,
                'ruhi_designs' => 0,
                'ruhi_products' => 0,
                'ruhi_kstones' => 0,
                'ruhi_design_categories' => 0,
                'ruhi_item_types' => 0,
            ];
        }

        $selectedOrganizationId = (int) session('current_organization_id', 0);
        $isSystemUser = ($authUser->user_type ?? '') === 'systemuser';
        $allowedOrgIds = $isSystemUser
            ? []
            : $authUser->organizations()->pluck('organizations.id')->all();
        $organizationScope = $selectedOrganizationId > 0 ? $selectedOrganizationId : null;
        $visibility = $this->getVisibility($authUser);

        $baseUserQuery = User::query()
            ->where(function (Builder $q) {
                $q->whereNull('user_type')
                    ->orWhere('user_type', '!=', 'systemuser');
            })
            ->when($selectedOrganizationId > 0, function (Builder $q) use ($selectedOrganizationId) {
                $q->whereHas('organizations', function (Builder $orgQ) use ($selectedOrganizationId) {
                    $orgQ->where('organizations.id', $selectedOrganizationId);
                });
            })
            ->when($selectedOrganizationId <= 0 && ! $isSystemUser, function (Builder $q) use ($allowedOrgIds) {
                if ($allowedOrgIds === []) {
                    $q->whereRaw('1 = 0');
                    return;
                }

                $q->whereHas('organizations', function (Builder $orgQ) use ($allowedOrgIds) {
                    $orgQ->whereIn('organizations.id', $allowedOrgIds);
                });
            });

        $operationalGeneralManagerCount = $visibility['operational_general_manager']
            ? (clone $baseUserQuery)->whereHas('designation', function (Builder $q) {
                $q->whereIn('name', ['Operational General Manager', 'Operational Genetal Manage']);
            })->count()
            : 0;

        $generalManagerCount = $visibility['general_manager']
            ? (clone $baseUserQuery)->whereHas('designation', function (Builder $q) {
                $q->where('name', 'General Manager');
            })->count()
            : 0;

        $areaManagerCount = $visibility['area_manager']
            ? (clone $baseUserQuery)->whereHas('designation', function (Builder $q) {
                $q->where('name', 'Area Manager');
            })->count()
            : 0;

        $accountantCount = $visibility['accountant']
            ? (clone $baseUserQuery)->whereHas('designation', function (Builder $q) {
                $q->where('name', 'Accountant');
            })->count()
            : 0;

        $outletsCount = $visibility['outlets']
            ? Outlet::query()
                ->when($selectedOrganizationId > 0, function (Builder $q) use ($selectedOrganizationId) {
                    $q->where('organization_id', $selectedOrganizationId);
                })
                ->when($selectedOrganizationId <= 0 && ! $isSystemUser, function (Builder $q) use ($allowedOrgIds) {
                    if ($allowedOrgIds === []) {
                        $q->whereRaw('1 = 0');
                        return;
                    }

                    $q->whereIn('organization_id', $allowedOrgIds);
                })
                ->count()
            : 0;

        $vendorsCount = $visibility['vendors']
            ? Vendor::query()
                ->when($selectedOrganizationId > 0, function (Builder $q) use ($selectedOrganizationId) {
                    $q->where('organization_id', $selectedOrganizationId);
                })
                ->when($selectedOrganizationId <= 0 && ! $isSystemUser, function (Builder $q) use ($allowedOrgIds) {
                    if ($allowedOrgIds === []) {
                        $q->whereRaw('1 = 0');
                        return;
                    }

                    $q->whereIn('organization_id', $allowedOrgIds);
                })
                ->count()
            : 0;

        $baseInvoiceQuery = Invoice::query()
            ->when($selectedOrganizationId > 0, function (Builder $q) use ($selectedOrganizationId) {
                $q->where('organization_id', $selectedOrganizationId);
            })
            ->when($selectedOrganizationId <= 0 && ! $isSystemUser, function (Builder $q) use ($allowedOrgIds) {
                if ($allowedOrgIds === []) {
                    $q->whereRaw('1 = 0');
                    return;
                }

                $q->whereIn('organization_id', $allowedOrgIds);
            });

        $this->applyInvoiceListScope($baseInvoiceQuery, $authUser, $organizationScope);

        $totalInvoiceCount = $visibility['total_invoice'] ? (clone $baseInvoiceQuery)->count() : 0;
        $completedInvoiceCount = $visibility['completed_invoice']
            ? (clone $baseInvoiceQuery)->whereIn('status', ['Complete', 'completed', 'Completed'])->count()
            : 0;
        $pendingInvoiceCount = $visibility['pending_invoice']
            ? (clone $baseInvoiceQuery)->whereIn('status', ['Pending', 'pending'])->count()
            : 0;
        $inProcessInvoiceCount = $visibility['in_process_invoice']
            ? (clone $baseInvoiceQuery)->whereIn('status', ['in_process', 'In Process', 'in process', 'processing'])->count()
            : 0;
        $approvedInvoiceCount = $visibility['approved_invoice']
            ? (clone $baseInvoiceQuery)->whereIn('status', ['Approve', 'approved', 'Approved'])->count()
            : 0;

        $ruhiGsCount = $visibility['ruhi_gs'] ? RuhiGs::query()->count() : 0;
        $ruhiDesignsCount = $visibility['ruhi_designs'] ? RuhiDesign::query()->count() : 0;
        $ruhiProductsCount = $visibility['ruhi_products'] ? RuhiProduct::query()->count() : 0;
        $ruhiKstonesCount = $visibility['ruhi_kstones'] ? RuhiKstone::query()->count() : 0;
        $ruhiDesignCategoriesCount = $visibility['ruhi_design_categories'] ? RuhiDesignCategory::query()->count() : 0;
        $ruhiItemTypesCount = $visibility['ruhi_item_types'] ? RuhiItemType::query()->count() : 0;

        return [
            'operational_general_manager' => $operationalGeneralManagerCount,
            'general_manager' => $generalManagerCount,
            'area_manager' => $areaManagerCount,
            'accountant' => $accountantCount,
            'outlets' => $outletsCount,
            'vendors' => $vendorsCount,
            'total_invoice' => $totalInvoiceCount,
            'completed_invoice' => $completedInvoiceCount,
            'pending_invoice' => $pendingInvoiceCount,
            'in_process_invoice' => $inProcessInvoiceCount,
            'approved_invoice' => $approvedInvoiceCount,
            'ruhi_gs' => $ruhiGsCount,
            'ruhi_designs' => $ruhiDesignsCount,
            'ruhi_products' => $ruhiProductsCount,
            'ruhi_kstones' => $ruhiKstonesCount,
            'ruhi_design_categories' => $ruhiDesignCategoriesCount,
            'ruhi_item_types' => $ruhiItemTypesCount,
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function getVisibility(?User $authUser): array
    {
        $keys = [
            'operational_general_manager',
            'general_manager',
            'area_manager',
            'accountant',
            'outlets',
            'vendors',
            'total_invoice',
            'completed_invoice',
            'pending_invoice',
            'in_process_invoice',
            'approved_invoice',
            'ruhi_gs',
            'ruhi_designs',
            'ruhi_products',
            'ruhi_kstones',
            'ruhi_design_categories',
            'ruhi_item_types',
        ];

        if (! $authUser) {
            return collect($keys)->mapWithKeys(fn (string $key) => [$key => false])->all();
        }

        if ($authUser->isSystemUser()) {
            return collect($keys)->mapWithKeys(fn (string $key) => [$key => true])->all();
        }

        $orgId = (int) session('current_organization_id', 0);
        $organizationScope = $orgId > 0 ? $orgId : null;
        $permissionMap = [
            'operational_general_manager' => 'dashboard-operational-manager',
            'general_manager' => 'dashboard-general-manager',
            'area_manager' => 'dashboard-area-manager',
            'accountant' => 'dashboard-accountant',
            'outlets' => 'dashboard-outlet',
            'vendors' => 'dashboard-vendors',
            'total_invoice' => 'dashboard-total-invoice',
            'completed_invoice' => 'dashboard-completed-invoice',
            'pending_invoice' => 'dashboard-pending-invoice',
            'in_process_invoice' => 'dashboard-in-process-invoice',
            'approved_invoice' => 'dashboard-approved-invoice',
            'ruhi_gs' => 'dashboard-manage-gs',
            'ruhi_designs' => 'dashboard-manage-design',
            'ruhi_products' => 'dashboard-manage-items',
            'ruhi_kstones' => 'dashboard-manage-kstone',
            'ruhi_design_categories' => 'dashboard-manage-design-category',
            'ruhi_item_types' => 'dashboard-manage-item-category',
        ];

        $visibility = [];
        foreach ($permissionMap as $key => $permissionName) {
            $visibility[$key] = $this->userHasDashboardPermission($authUser, $permissionName, $organizationScope);
        }

        return $visibility;
    }

    private function applyInvoiceListScope(Builder $query, User $user, ?int $organizationId): void
    {
        if (! InvoiceDepartmentAuthorization::userHasListInOrganization($user, $organizationId)) {
            $query->whereRaw('1 = 0');
            return;
        }

        if (InvoiceDepartmentAuthorization::listOwnInvoicesOnly($user, $organizationId)) {
            $query->where('createdby_id', (int) $user->id);
            return;
        }

        if (InvoiceDepartmentAuthorization::listReportingInvoicesOnly($user, $organizationId)) {
            $includeSubordinates = InvoiceDepartmentAuthorization::listReportingInvoicesIncludeSubordinates($user, $organizationId);
            $reportingUserIds = InvoiceDepartmentAuthorization::reportingUserIds($user, $includeSubordinates);
            if ($reportingUserIds === []) {
                $query->whereRaw('1 = 0');
                return;
            }
            $query->whereIn('createdby_id', array_map('intval', $reportingUserIds));
            return;
        }

        $restriction = InvoiceDepartmentAuthorization::mergedListDepartmentRestriction($user, $organizationId);
        if ($restriction === null) {
            return;
        }

        if ($restriction === []) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereIn('department_id', array_map('intval', $restriction));
    }

    private function userHasDashboardPermission(User $user, string $permissionName, ?int $organizationId): bool
    {
        if ($user->hasDirectPermission($permissionName)) {
            return true;
        }

        if ($organizationId !== null && $user->hasPermissionInOrganization($permissionName, $organizationId)) {
            return true;
        }

        if ($organizationId === null) {
            return $user->can($permissionName);
        }

        return false;
    }
}
