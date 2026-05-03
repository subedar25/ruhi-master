<?php

// database/seeders/ModuleAndPermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Permission;
use Illuminate\Support\Facades\DB; // Optional, for a clean reset

class ModuleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Define all your modules and permissions in one place
        $modulesAndPermissions = [
            'User Management' => [
                'permissions' => [
                    ['name' => 'create-user', 'display_name' => 'Create User','slug' => 'create-user'],
                    ['name' => 'edit-user', 'display_name' => 'Edit User','slug' => 'edit-user'],
                    // as discuss with client, we don't need to delete user
                    // ['name' => 'delete-user', 'display_name' => 'Delete User','slug' => 'delete-user'],
                    ['name' => 'edit-email', 'display_name' => 'Edit Email','slug' => 'edit-email'],
                    ['name' => 'list-users', 'display_name' => 'View Users','slug' => 'list-users'],
                   // ['name' => 'list-wordpress-user', 'display_name' => 'View WordPress User', 'slug' => 'list-wordpress-user'],
                    //create active user,drivers, wordpress permissions
                    ['name' => 'active-deactive', 'display_name' => 'Active-Deactive User','slug' => 'active-deactive'],
                ],
                'slug' => 'user-management'
            ],
             'Roles Management' => [
                'permissions' => [
                    ['name' => 'create-role', 'display_name' => 'Create Role','slug' => 'create-role'],
                    ['name' => 'edit-role', 'display_name' => 'Edit Role','slug' => 'edit-role'],
                    ['name' => 'delete-role', 'display_name' => 'Delete Role','slug' => 'delete-role'],
                    ['name' => 'list-role', 'display_name' => 'View Roles','slug' => 'list-role'],
                ],
                'slug' => 'roles-management'
            ],

            'Permission Management' => [
                'type' => 'system',
                'permissions' => [
                    ['name' => 'create-permission', 'display_name' => 'Create Permission','slug' => 'create-permission'],
                    ['name' => 'edit-permission', 'display_name' => 'Edit Permission','slug' => 'edit-permission'],
                    ['name' => 'delete-permission', 'display_name' => 'Delete Permission','slug' => 'delete-permission'],
                    ['name' => 'list-permission', 'display_name' => 'View Permission','slug' => 'list-permission'],
                    ['name' => 'activate-deactivate-permission', 'display_name' => 'Activate/Deactivate Permission','slug' => 'activate-deactivate-permission'],
                ],
                'slug' => 'permission-management'
            ],

            'Modules Management' => [
                'type' => 'system',
                'permissions' => [
                    ['name' => 'create-modules', 'display_name' => 'Create Modules','slug' => 'create-modules'],
                    ['name' => 'edit-modules', 'display_name' => 'Edit Modules','slug' => 'edit-modules'],
                    ['name' => 'delete-modules', 'display_name' => 'Delete Modules','slug' => 'delete-modules'],
                    ['name' => 'list-modules', 'display_name' => 'View Modules','slug' => 'list-modules'],
                ],
                'slug' => 'modules-management'
            ],
            // 'Client Management' => [
            //     'permissions' => [
            //         ['name' => 'create-client', 'display_name' => 'Create Client','slug' => 'create-client'],
            //         ['name' => 'edit-client', 'display_name' => 'Edit Client','slug' => 'edit-client'],
            //         ['name' => 'delete-client', 'display_name' => 'Delete Client','slug' => 'delete-client'],
            //         ['name' => 'list-client', 'display_name' => 'View Client','slug' => 'list-client'],
            //     ],
            //     'slug' => 'client-management'
            // ],

            // Hide driver and wordpress user management after discussing with client
            // 'Driver Management' => [
            //     'permissions' => [
            //         ['name' => 'create-driver', 'display_name' => 'Create driver','slug' => 'create-driver'],
            //         ['name' => 'edit-driver', 'display_name' => 'Edit driver','slug' => 'edit-driver'],
            //         ['name' => 'delete-driver', 'display_name' => 'Delete driver','slug' => 'delete-driver'],
            //         ['name' => 'list-driver', 'display_name' => 'View driver','slug' => 'list-driver'],
            //     ],
            //     'slug' => 'driver-management'
            // ],
            //  'Wordpress User Management' => [
            //     'permissions' => [
            //         ['name' => 'create-wordpress-user', 'display_name' => 'Create Wordpress User','slug' => 'create-wordpress-user'],
            //         ['name' => 'edit-wordpress-user', 'display_name' => 'Edit Wordpress User','slug' => 'edit-wordpress-user'],
            //         ['name' => 'delete-wordpress-user', 'display_name' => 'Delete Wordpress User','slug' => 'delete-wordpress-user'],
            //         ['name' => 'list-wordpress-user', 'display_name' => 'View Wordpress User','slug' => 'list-wordpress-user'],
            //     ],
            //     'slug' => 'wordpress-user--management'
            // ],

            // 'Vehicle Management' => [
            //     'permissions' => [
            //         ['name' => 'create-vehicle', 'display_name' => 'Create vehicle','slug' => 'create-vehicle'],
            //         ['name' => 'edit-vehicle', 'display_name' => 'Edit vehicle','slug' => 'edit-vehicle'],
            //         ['name' => 'delete-vehicle', 'display_name' => 'Delete vehicle','slug' => 'delete-vehicle'],
            //         ['name' => 'list-vehicle', 'display_name' => 'View vehicle','slug' => 'list-vehicle'],
            //     ],
            //     'slug' => 'vehicle-management'
            // ],

            //  'Timesheet Management' => [
            //     'permissions' => [
            //         ['name' => 'create-timesheet', 'display_name' => 'Create Timesheet','slug' => 'create-timesheet'],
            //         ['name' => 'edit-timesheet', 'display_name' => 'Edit Timesheet','slug' => 'edit-timesheet'],
            //         ['name' => 'delete-timesheet', 'display_name' => 'Delete Timesheet','slug' => 'delete-timesheet'],
            //         ['name' => 'list-timesheets', 'display_name' => 'View Timesheet','slug' => 'list-timesheets'],
            //     ],
            //     'slug' => 'timesheet-management'

            // ],
            // 'Time Off Request Management' => [
            //     'permissions' => [
            //         ['name' => 'create-time-off-request', 'display_name' => 'Create Time Off Request', 'slug' => 'create-time-off-request'],
            //         ['name' => 'edit-time-off-request', 'display_name' => 'Edit Time Off Request', 'slug' => 'edit-time-off-request'],
            //         ['name' => 'delete-time-off-request', 'display_name' => 'Delete Time Off Request', 'slug' => 'delete-time-off-request'],
            //         ['name' => 'list-time-off-requests', 'display_name' => 'List Time Off Requests', 'slug' => 'list-time-off-requests'],
            //         ['name' => 'status-time-off-request', 'display_name' => 'Change Status Time Off Request', 'slug' => 'status-time-off-request'],
            //         ['name' => 'admin-time-off-requests', 'display_name' => 'Admin Time Off Request', 'slug' => 'admin-time-off-requests'],
            //     ],
            //     'slug' => 'time-off-request-management'
            // ],
            // 'Droppoint Management' => [
            //     'permissions' => [
            //         ['name' => 'create-dropoint', 'display_name' => 'Create dropoint','slug' => 'create-dropoint'],
            //         ['name' => 'edit-dropoint', 'display_name' => 'Edit dropoint','slug' => 'edit-dropoint'],
            //         ['name' => 'delete-dropoint', 'display_name' => 'Delete dropoint','slug' => 'delete-dropoint'],
            //         ['name' => 'list-dropoint', 'display_name' => 'View dropoint','slug' => 'list-dropoint'],
            //     ],
            //     'slug' => 'dropoint-management'
            // ],

            // Location Management Module and Permissions
            // 'Location Management' => [
            //     'permissions' => [
            //         ['name' => 'create-location', 'display_name' => 'Create Location', 'slug' => 'create-location'],
            //         ['name' => 'edit-location', 'display_name' => 'Edit Location', 'slug' => 'edit-location'],
            //         ['name' => 'delete-location', 'display_name' => 'Delete Location', 'slug' => 'delete-location'],
            //         ['name' => 'list-locations', 'display_name' => 'View Locations', 'slug' => 'list-locations'],
            //     ],
            //     'slug' => 'location-management'
            // ],

            'Invoice Management' => [
                'permissions' => [
                    ['name' => 'create-invoice', 'display_name' => 'Create Invoice', 'slug' => 'create-invoice'],
                    ['name' => 'edit-invoice', 'display_name' => 'Edit Invoice', 'slug' => 'edit-invoice'],
                    ['name' => 'after-approval-change-edit-invoice', 'display_name' => 'After Approval Change Edit Invoice', 'slug' => 'after-approval-change-edit-invoice'],
                    ['name' => 'delete-invoice', 'display_name' => 'Delete Invoice', 'slug' => 'delete-invoice'],
                    ['name' => 'list-invoices', 'display_name' => 'View Invoices', 'slug' => 'list-invoices'],
                    ['name' => 'approve-invoice', 'display_name' => 'Approve Invoice', 'slug' => 'approve-invoice'],
                    ['name' => 'make-payment', 'display_name' => 'Make Payment', 'slug' => 'make-payment'],
                    ['name' => 'view-payment-history', 'display_name' => 'View Payment History', 'slug' => 'view-payment-history'],
                    ['name' => 'change-payment-status', 'display_name' => 'Change Payment Status', 'slug' => 'change-payment-status'],
                ],
                'slug' => 'invoice-management'
            ],
            'Dashboard' => [
                'permissions' => [
                    ['name' => 'dashboard-operational-manager', 'display_name' => 'Dashboard Operational Manager', 'slug' => 'dashboard-operational-manager'],
                    ['name' => 'dashboard-general-manager', 'display_name' => 'Dashboard General Manager', 'slug' => 'dashboard-general-manager'],
                    ['name' => 'dashboard-area-manager', 'display_name' => 'Dashboard Area Manager', 'slug' => 'dashboard-area-manager'],
                    ['name' => 'dashboard-accountant', 'display_name' => 'Dashboard Accountant', 'slug' => 'dashboard-accountant'],
                    ['name' => 'dashboard-outlet', 'display_name' => 'Dashboard Outlet', 'slug' => 'dashboard-outlet'],
                    ['name' => 'dashboard-vendors', 'display_name' => 'Dashboard Vendors', 'slug' => 'dashboard-vendors'],
                    ['name' => 'dashboard-total-invoice', 'display_name' => 'Dashboard Total Invoice', 'slug' => 'dashboard-total-invoice'],
                    ['name' => 'dashboard-completed-invoice', 'display_name' => 'Dashboard Completed Invoice', 'slug' => 'dashboard-completed-invoice'],
                    ['name' => 'dashboard-pending-invoice', 'display_name' => 'Dashboard Pending Invoice', 'slug' => 'dashboard-pending-invoice'],
                    ['name' => 'dashboard-in-process-invoice', 'display_name' => 'Dashboard In Process Invoice', 'slug' => 'dashboard-in-process-invoice'],
                    ['name' => 'dashboard-approved-invoice', 'display_name' => 'Dashboard Approved Invoice', 'slug' => 'dashboard-approved-invoice'],
                    ['name' => 'dashboard-manage-gs', 'display_name' => 'Dashboard Manage GS', 'slug' => 'dashboard-manage-gs'],
                    ['name' => 'dashboard-manage-design', 'display_name' => 'Dashboard Manage Design', 'slug' => 'dashboard-manage-design'],
                    ['name' => 'dashboard-manage-items', 'display_name' => 'Dashboard Manage Items', 'slug' => 'dashboard-manage-items'],
                    ['name' => 'dashboard-manage-kstone', 'display_name' => 'Dashboard Manage Kstone', 'slug' => 'dashboard-manage-kstone'],
                    ['name' => 'dashboard-manage-design-category', 'display_name' => 'Dashboard Manage Design Category', 'slug' => 'dashboard-manage-design-category'],
                    ['name' => 'dashboard-manage-item-category', 'display_name' => 'Dashboard Manage Item Category', 'slug' => 'dashboard-manage-item-category'],
                ],
                'slug' => 'dashboard'
            ],

            // 'Contact Management' => [
            //     'permissions' => [
            //         ['name' => 'create-contact', 'display_name' => 'Create contact','slug' => 'create-contact'],
            //         ['name' => 'edit-contact', 'display_name' => 'Edit contact','slug' => 'edit-contact'],
            //         ['name' => 'delete-contact', 'display_name' => 'Delete contact','slug' => 'delete-contact'],
            //         ['name' => 'list-contact', 'display_name' => 'View contact','slug' => 'list-contact'],
            //         ['name' => 'list-contact-item', 'display_name' => 'View contact Items','slug' => 'list-contact-item'],
            //         ['name' => 'create-contact-item', 'display_name' => 'Create contact item','slug' => 'create-contact-item'],
            //         ['name' => 'edit-contact-item', 'display_name' => 'Edit contact item','slug' => 'edit-contact-item'],
            //         ['name' => 'delete-contact-item', 'display_name' => 'Delete contact item','slug' => 'delete-contact-item'],
            //     ],
            //     'slug' => 'contact-management'
            // ],
            'Organization Management' => [
                'type' => 'system',
                'permissions' => [
                    ['name' => 'create-organization', 'display_name' => 'Create organization','slug' => 'create-organization'],
                    ['name' => 'edit-organization', 'display_name' => 'Edit organization','slug' => 'edit-organization'],
                    ['name' => 'delete-organization', 'display_name' => 'Delete organization','slug' => 'delete-organization'],
                    ['name' => 'list-organization', 'display_name' => 'View organization','slug' => 'list-organization'],
                    ['name' => 'activate-deactivate-organization', 'display_name' => 'Activate/Deactivate organization','slug' => 'activate-deactivate-organization'],
                ],
                'slug' => 'organization-management'
            ],
            // 'Price Structure Type Management' => [
            //     'permissions' => [
            //         ['name' => 'create-price-structure-type', 'display_name' => 'Create Price Structure Type', 'slug' => 'create-price-structure-type'],
            //         ['name' => 'edit-price-structure-type', 'display_name' => 'Edit Price Structure Type', 'slug' => 'edit-price-structure-type'],
            //         ['name' => 'delete-price-structure-type', 'display_name' => 'Delete Price Structure Type', 'slug' => 'delete-price-structure-type'],
            //         ['name' => 'list-price-structure-type', 'display_name' => 'View Price Structure Type', 'slug' => 'list-price-structure-type'],
            //     ],
            //     'slug' => 'price-structure-type-management'
            // ],
            // 'Issue Orientations' => [
            //     'permissions' => [
            //         ['name' => 'add-issue-orientation', 'display_name' => 'Add Orientation', 'slug' => 'add-issue-orientation'],
            //         ['name' => 'toggle-issue-orientation', 'display_name' => 'Active/Inactive Toggle (Orientations)', 'slug' => 'toggle-issue-orientation'],
            //         ['name' => 'edit-issue-orientation', 'display_name' => 'Edit Orientation', 'slug' => 'edit-issue-orientation'],
            //         ['name' => 'delete-issue-orientation', 'display_name' => 'Delete Orientation', 'slug' => 'delete-issue-orientation'],
            //     ],
            //     'slug' => 'issue-orientations'
            // ],
            // 'Issue Sections' => [
            //     'permissions' => [
            //         ['name' => 'add-issue-section', 'display_name' => 'Add Section', 'slug' => 'add-issue-section'],
            //         ['name' => 'toggle-issue-section', 'display_name' => 'Active/Inactive Toggle (Sections)', 'slug' => 'toggle-issue-section'],
            //         ['name' => 'edit-issue-section', 'display_name' => 'Edit Section', 'slug' => 'edit-issue-section'],
            //         ['name' => 'delete-issue-section', 'display_name' => 'Delete Section', 'slug' => 'delete-issue-section'],
            //     ],
            //     'slug' => 'issue-sections'
            // ],
            'Audit Log' => [
                'permissions' => [
                    ['name' => 'list-auditlog', 'display_name' => 'View Audit Log','slug' => 'list-auditlog'],
                ],
                'slug' => 'auditlog-management'
            ],
            // 'Two-Factor Authentication' => [
            //     'permissions' => [
            //         ['name' => 'enable-two-factor', 'display_name' => 'Enable Two-Factor Authentication','slug' => 'enable-two-factor']
            //     ],
            //     'slug' => 'two-factor-authentication'
            // ],
            //Master permissions
            'Master Management' => [
                'permissions' => [
                    ['name' => 'list-master', 'display_name' => 'View Masters','slug' => 'list-master'],
                    ['name' => 'department', 'display_name' => 'Department', 'slug' => 'department'],
                    ['name' => 'edit-department', 'display_name' => 'Edit Department', 'slug' => 'edit-department'],
                    ['name' => 'delete-department', 'display_name' => 'Delete Department', 'slug' => 'delete-department'],
                    ['name' => 'locations', 'display_name' => 'Locations', 'slug' => 'locations'],
                    ['name' => 'edit-location', 'display_name' => 'Edit Location', 'slug' => 'edit-location'],
                    ['name' => 'delete-location', 'display_name' => 'Delete Location', 'slug' => 'delete-location'],
                    ['name' => 'vendors', 'display_name' => 'Vendors', 'slug' => 'vendors'],
                    ['name' => 'edit-vendor', 'display_name' => 'Edit Vendor', 'slug' => 'edit-vendor'],
                    ['name' => 'delete-vendor', 'display_name' => 'Delete Vendor', 'slug' => 'delete-vendor'],
                    ['name' => 'edit-vendor-category', 'display_name' => 'Edit Vendor Category', 'slug' => 'edit-vendor-category'],
                    ['name' => 'delete-vendor-category', 'display_name' => 'Delete Vendor Category', 'slug' => 'delete-vendor-category'],
                    ['name' => 'outlets', 'display_name' => 'Outlets', 'slug' => 'outlets'],
                    ['name' => 'edit-outlet', 'display_name' => 'Edit Outlet', 'slug' => 'edit-outlet'],
                    ['name' => 'delete-outlet', 'display_name' => 'Delete Outlet', 'slug' => 'delete-outlet'],
                    ['name' => 'country', 'display_name' => 'Country','slug' => 'country'],
                    ['name' => 'edit-country', 'display_name' => 'Edit Country', 'slug' => 'edit-country'],
                    ['name' => 'delete-country', 'display_name' => 'Delete Country', 'slug' => 'delete-country'],
                    ['name' => 'state', 'display_name' => 'State','slug' => 'state'],
                    ['name' => 'edit-state', 'display_name' => 'Edit State', 'slug' => 'edit-state'],
                    ['name' => 'delete-state', 'display_name' => 'Delete State', 'slug' => 'delete-state'],
                    ['name' => 'products', 'display_name' => 'Products','slug' => 'products'],
                    ['name' => 'edit-product', 'display_name' => 'Edit Product', 'slug' => 'edit-product'],
                    ['name' => 'delete-product', 'display_name' => 'Delete Product', 'slug' => 'delete-product'],
                    ['name' => 'taxes', 'display_name' => 'Taxes','slug' => 'taxes'],
                    ['name' => 'designation', 'display_name' => 'Designation', 'slug' => 'designation'],
                    ['name' => 'edit-designation', 'display_name' => 'Edit Designation', 'slug' => 'edit-designation'],
                    ['name' => 'delete-designation', 'display_name' => 'Delete Designation', 'slug' => 'delete-designation'],
                    
                ],
                'slug' => 'master-management'
            ],
            'Ruhi Masters' => [
                'permissions' => [
                    ['name' => 'list-ruhi-items', 'display_name' => 'View Ruhi Items', 'slug' => 'list-ruhi-items'],
                    ['name' => 'create-ruhi-item', 'display_name' => 'Create Ruhi Item', 'slug' => 'create-ruhi-item'],
                    ['name' => 'edit-ruhi-item', 'display_name' => 'Edit Ruhi Item', 'slug' => 'edit-ruhi-item'],
                    ['name' => 'delete-ruhi-item', 'display_name' => 'Delete Ruhi Item', 'slug' => 'delete-ruhi-item'],

                    ['name' => 'list-ruhi-collet-kstones', 'display_name' => 'View Collet K-Stones', 'slug' => 'list-ruhi-collet-kstones'],
                    ['name' => 'create-ruhi-collet-kstone', 'display_name' => 'Create Collet K-Stone', 'slug' => 'create-ruhi-collet-kstone'],
                    ['name' => 'edit-ruhi-collet-kstone', 'display_name' => 'Edit Collet K-Stone', 'slug' => 'edit-ruhi-collet-kstone'],
                    ['name' => 'delete-ruhi-collet-kstone', 'display_name' => 'Delete Collet K-Stone', 'slug' => 'delete-ruhi-collet-kstone'],

                    ['name' => 'list-ruhi-designs', 'display_name' => 'View Ruhi Designs', 'slug' => 'list-ruhi-designs'],
                    ['name' => 'create-ruhi-design', 'display_name' => 'Create Ruhi Design', 'slug' => 'create-ruhi-design'],
                    ['name' => 'edit-ruhi-design', 'display_name' => 'Edit Ruhi Design', 'slug' => 'edit-ruhi-design'],
                    ['name' => 'delete-ruhi-design', 'display_name' => 'Delete Ruhi Design', 'slug' => 'delete-ruhi-design'],

                    ['name' => 'list-ruhi-design-products', 'display_name' => 'View Design Products', 'slug' => 'list-ruhi-design-products'],
                    ['name' => 'create-ruhi-design-product', 'display_name' => 'Create Design Product', 'slug' => 'create-ruhi-design-product'],
                    ['name' => 'edit-ruhi-design-product', 'display_name' => 'Edit Design Product', 'slug' => 'edit-ruhi-design-product'],
                    ['name' => 'delete-ruhi-design-product', 'display_name' => 'Delete Design Product', 'slug' => 'delete-ruhi-design-product'],

                    ['name' => 'list-ruhi-gs', 'display_name' => 'View Ruhi GS', 'slug' => 'list-ruhi-gs'],
                    ['name' => 'create-ruhi-gs', 'display_name' => 'Create Ruhi GS', 'slug' => 'create-ruhi-gs'],
                    ['name' => 'edit-ruhi-gs', 'display_name' => 'Edit Ruhi GS', 'slug' => 'edit-ruhi-gs'],
                    ['name' => 'delete-ruhi-gs', 'display_name' => 'Delete Ruhi GS', 'slug' => 'delete-ruhi-gs'],

                    ['name' => 'list-ruhi-gs-lots', 'display_name' => 'View GS Lots', 'slug' => 'list-ruhi-gs-lots'],
                    ['name' => 'create-ruhi-gs-lot', 'display_name' => 'Create GS Lot', 'slug' => 'create-ruhi-gs-lot'],
                    ['name' => 'edit-ruhi-gs-lot', 'display_name' => 'Edit GS Lot', 'slug' => 'edit-ruhi-gs-lot'],
                    ['name' => 'delete-ruhi-gs-lot', 'display_name' => 'Delete GS Lot', 'slug' => 'delete-ruhi-gs-lot'],

                    ['name' => 'list-ruhi-kstones', 'display_name' => 'View K Stones', 'slug' => 'list-ruhi-kstones'],
                    ['name' => 'create-ruhi-kstone', 'display_name' => 'Create K Stone', 'slug' => 'create-ruhi-kstone'],
                    ['name' => 'edit-ruhi-kstone', 'display_name' => 'Edit K Stone', 'slug' => 'edit-ruhi-kstone'],
                    ['name' => 'delete-ruhi-kstone', 'display_name' => 'Delete K Stone', 'slug' => 'delete-ruhi-kstone'],

                    ['name' => 'list-ruhi-design-categories', 'display_name' => 'View Design Categories', 'slug' => 'list-ruhi-design-categories'],
                    ['name' => 'create-ruhi-design-category', 'display_name' => 'Create Design Category', 'slug' => 'create-ruhi-design-category'],
                    ['name' => 'edit-ruhi-design-category', 'display_name' => 'Edit Design Category', 'slug' => 'edit-ruhi-design-category'],
                    ['name' => 'delete-ruhi-design-category', 'display_name' => 'Delete Design Category', 'slug' => 'delete-ruhi-design-category'],

                    ['name' => 'list-ruhi-item-types', 'display_name' => 'View Item Categories', 'slug' => 'list-ruhi-item-types'],
                    ['name' => 'create-ruhi-item-type', 'display_name' => 'Create Item Category', 'slug' => 'create-ruhi-item-type'],
                    ['name' => 'edit-ruhi-item-type', 'display_name' => 'Edit Item Category', 'slug' => 'edit-ruhi-item-type'],
                    ['name' => 'delete-ruhi-item-type', 'display_name' => 'Delete Item Category', 'slug' => 'delete-ruhi-item-type'],
                ],
                'slug' => 'ruhi-masters',
            ],
            'Ruhi Reports' => [
                'permissions' => [
                    ['name' => 'view-ruhi-report-gs-wise-casting', 'display_name' => 'GS Wise Casting Report', 'slug' => 'view-ruhi-report-gs-wise-casting'],
                    ['name' => 'view-ruhi-report-gs-wise-casting-detail', 'display_name' => 'GS Wise Casting Detail Report', 'slug' => 'view-ruhi-report-gs-wise-casting-detail'],
                    ['name' => 'view-ruhi-report-gs-wise-dubby', 'display_name' => 'GS Wise Dubby Report', 'slug' => 'view-ruhi-report-gs-wise-dubby'],
                    ['name' => 'view-ruhi-report-gs-wise-collet', 'display_name' => 'GS Wise Collet Report', 'slug' => 'view-ruhi-report-gs-wise-collet'],
                    ['name' => 'view-ruhi-report-gs-full', 'display_name' => 'GS Full Report', 'slug' => 'view-ruhi-report-gs-full'],
                    ['name' => 'view-ruhi-report-gs-die', 'display_name' => 'GS Die Report', 'slug' => 'view-ruhi-report-gs-die'],
                    ['name' => 'view-ruhi-report-gs-detail-each-item', 'display_name' => 'GS Wise Detail Report of Each Item', 'slug' => 'view-ruhi-report-gs-detail-each-item'],
                    ['name' => 'view-ruhi-report-gs-color-collet', 'display_name' => 'GS Color Collet Report', 'slug' => 'view-ruhi-report-gs-color-collet'],
                    ['name' => 'view-ruhi-report-gs-wise-drop', 'display_name' => 'GS Wise Drop Report', 'slug' => 'view-ruhi-report-gs-wise-drop'],
                    ['name' => 'view-ruhi-report-gs-color-full', 'display_name' => 'GS Color Full Report', 'slug' => 'view-ruhi-report-gs-color-full'],
                    ['name' => 'view-ruhi-report-gs-collet-kstone-color', 'display_name' => 'GS Wise Collet Kstone Color Report', 'slug' => 'view-ruhi-report-gs-collet-kstone-color'],
                ],
                'slug' => 'ruhi-reports',
            ],
            ];
          


        $guardName = 'web';

      

        foreach ($modulesAndPermissions as $moduleName => $data) {
            $moduleType = $data['type'] ?? 'public';

            // Use updateOrCreate to avoid creating duplicates and keep names in sync.
            $module = Module::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $moduleName,
                    'type' => $moduleType,
                ]
            );

            foreach ($data['permissions'] as $permissionData) {
                $permissionType = $permissionData['type'] ?? $moduleType;

                // Use updateOrCreate for permissions as well to prevent duplicates.
                Permission::updateOrCreate(
                    // Find by the unique name
                    ['name' => $permissionData['name'], 'guard_name' => $guardName],
                    // Create with these details if not found
                    [
                        'display_name' => $permissionData['display_name'],
                        'module_id' => $module->id,
                        'guard_name' => $guardName,
                        'slug' => $permissionData['slug'],
                        'is_active' => true,
                        'type' => $permissionType,
                    ]
                );
            }
        }

        // Remove deprecated permissions that should no longer appear in role forms.
        Permission::query()
            ->where('guard_name', $guardName)
            ->whereIn('name', ['list-driver'])
            ->delete();

    }
}
