@php
    $from = request()->get('from');
@endphp


@auth
<!-- Dashboard -->
@if(\App\Support\OrganizationModule::sidebarShow('dashboard'))
<li class="nav-item">
    <a href="{{ route('masterapp.dashboard') }}" class="nav-link {{ request()->routeIs('masterapp.dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>
@endif
@if(\App\Support\OrganizationModule::sidebarShow('ruhi-masters'))
<li class="nav-item has-treeview {{ request()->routeIs('masterapp.ruhi-items.*', 'masterapp.ruhi-designs.*', 'masterapp.ruhi-design-categories.*', 'masterapp.ruhi-item-types.*', 'masterapp.ruhi-gs.*', 'masterapp.ruhi-kstones.*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->routeIs('masterapp.ruhi-items.*', 'masterapp.ruhi-designs.*', 'masterapp.ruhi-design-categories.*', 'masterapp.ruhi-item-types.*', 'masterapp.ruhi-gs.*', 'masterapp.ruhi-kstones.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cubes"></i>
        <p>
            Ruhi Master
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview nav-treeview-inset ml-2" style="padding-right: 1.25rem;">
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-items.index') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-items.*') ? 'active' : '' }}">
                <i class="fas fa-boxes nav-icon"></i>
                <p>Manage Item</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-designs.index') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-designs.*') ? 'active' : '' }}">
                <i class="fas fa-drafting-compass nav-icon"></i>
                <p>Manage Design</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-gs.index') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-gs.*') ? 'active' : '' }}">
                <i class="fas fa-gem nav-icon"></i>
                <p>Manage GS</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-kstones.index') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-kstones.*') ? 'active' : '' }}">
                <i class="fas fa-shapes nav-icon"></i>
                <p>Manage K Stone</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-design-categories.index') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-design-categories.*') ? 'active' : '' }}">
                <i class="fas fa-tags nav-icon"></i>
                <p>Design Category</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-item-types.index') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-item-types.*') ? 'active' : '' }}">
                <i class="fas fa-th-list nav-icon"></i>
                <p>Item Catogory</p>
            </a>
        </li>
    </ul>
</li>
@endif
@if(\App\Support\OrganizationModule::sidebarShow('ruhi-reports'))
<li class="nav-item has-treeview {{ request()->routeIs('masterapp.ruhi-reports.*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-bar"></i>
        <p>
            Ruhi Report
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview nav-treeview-inset ml-2" style="padding-right: 1.25rem;">
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-lot-wise-items') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-lot-wise-items') ? 'active' : '' }}">
                <i class="fas fa-layer-group nav-icon"></i>
                <p>Gs-Lot Wise Items</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-wise-casting-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-wise-casting-report') ? 'active' : '' }}">
                <i class="fas fa-flask nav-icon"></i>
                <p>Gs Wise Casting Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-wise-casting-detail-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-wise-casting-detail-report') ? 'active' : '' }}">
                <i class="fas fa-list-ul nav-icon"></i>
                <p>Gs Wise Casting Detail Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-wise-dubby-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-wise-dubby-report') ? 'active' : '' }}">
                <i class="fas fa-cubes nav-icon"></i>
                <p>GS Wise Dubby Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-wise-collet-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-wise-collet-report') ? 'active' : '' }}">
                <i class="fas fa-link nav-icon"></i>
                <p>GS Wise Collet Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-full-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-full-report') ? 'active' : '' }}">
                <i class="fas fa-border-all nav-icon"></i>
                <p>GS Full Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-die-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-die-report') ? 'active' : '' }}">
                <i class="fas fa-tools nav-icon"></i>
                <p>GS Die Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-detail-each-item-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-detail-each-item-report') ? 'active' : '' }}">
                <i class="fas fa-list-alt nav-icon"></i>
                <p>GS Detail of Each Item</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-color-collet-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-color-collet-report') ? 'active' : '' }}">
                <i class="fas fa-palette nav-icon"></i>
                <p>GS Color Collet Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-wise-drop-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-wise-drop-report') ? 'active' : '' }}">
                <i class="fas fa-tint nav-icon"></i>
                <p>GS Wise Drop Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-color-full-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-color-full-report') ? 'active' : '' }}">
                <i class="fas fa-fill-drip nav-icon"></i>
                <p>GS Color Full Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('masterapp.ruhi-reports.gs-collet-kstone-color-report') }}" class="nav-link {{ request()->routeIs('masterapp.ruhi-reports.gs-collet-kstone-color-report') ? 'active' : '' }}">
                <i class="fas fa-gem nav-icon"></i>
                <p>GS Wise Collet Kstone Color Report</p>
            </a>
        </li>
    </ul>
</li>
@endif
<!-- Invoices -->
@can('list-invoices')
@if(\App\Support\OrganizationModule::sidebarShow('invoice-management'))
<li class="nav-item">
    <a href="{{ route('invoice.index') }}" class="nav-link {{ request()->routeIs('invoice.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-invoice-dollar"></i>
        <p>Invoices</p>
    </a>
</li>
@endif
@endcan
<!-- Users -->
@canany(['list-users'])
@if(\App\Support\OrganizationModule::sidebarShow('user-management'))
<li class="nav-item has-treeview {{ request()->routeIs('masterapp.users.*', 'masterapp.entity.*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->routeIs('masterapp.users.*', 'masterapp.entity.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users-cog"></i>
        <p>
            Manage Users
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview nav-treeview-inset ml-2" style="padding-right: 1.25rem;">
        @can('list-users')
        <li class="nav-item">
            <a href="{{ route('masterapp.users.index') }}" class="nav-link {{ request()->routeIs('masterapp.users.*') || (request()->routeIs('masterapp.entity.*') && $from === 'users') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <p>Users</p>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endif
@endcanany
@can('list-timesheets')
<!-- <li class="nav-item">
    <a href="{{ route('masterapp.timesheets.index') }}" class="nav-link {{ request()->routeIs('masterapp.timesheets.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-clock"></i>
        <p>Manage Timesheets</p>
    </a>
</li> -->
@endcan
@can('list-time-off-requests')
<!-- <li class="nav-item">
    <a href="{{ route('masterapp.time-off-requests.index') }}" class="nav-link {{ request()->routeIs('masterapp.time-off-requests.*') ? 'active' : '' }}">
        {{-- <i class="nav-icon fas fa-calendar-times-o"></i> --}}
        <i class="nav-icon fas fa-calendar-times"></i>
        <p>Manage Time-off</p>
    </a>
</li> -->
@endcan



@can('list-role')
@if(\App\Support\OrganizationModule::sidebarShow('roles-management'))
<li class="nav-item">
    <a href="{{ route('masterapp.roles.index') }}" class="nav-link {{ request()->routeIs('masterapp.roles.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-tag"></i>
        <p>Manage Role</p>
    </a>
</li>
@endif
@endcan

@can('list-modules')
@if(\App\Support\OrganizationModule::sidebarShow('modules-management'))
<li class="nav-item">
    <a href="{{ route('masterapp.modules.index') }}" class="nav-link {{ request()->routeIs('masterapp.modules.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-layer-group"></i>
        <p>Manage Modules</p>
    </a>
</li>
@endif
@endcan

@can('list-permission')
@if(\App\Support\OrganizationModule::sidebarShow('permission-management'))
<li class="nav-item">
    <a href="{{ route('masterapp.permissions.index') }}" class="nav-link {{ request()->routeIs('masterapp.permissions.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-key"></i>
        <p>Manage Permissions</p>
    </a>
</li>
@endif
@endcan
@can('list-master')
@if(\App\Support\OrganizationModule::sidebarShow('master-management'))
<li class="nav-item">
    <a href="{{ route('masterapp.masters') }}" class="nav-link {{ request()->routeIs('masterapp.masters') ? 'active' : '' }}">
        <i class="nav-icon fas fa-database"></i>
        <p>Masters</p>
    </a>
</li>
@endif
@endcan
@endauth

