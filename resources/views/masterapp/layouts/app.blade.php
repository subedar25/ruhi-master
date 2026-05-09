<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $documentOrgName = isset($orgSwitcherCurrentOrganization) && $orgSwitcherCurrentOrganization
            ? trim((string) ($orgSwitcherCurrentOrganization->name ?? ''))
            : '';
    @endphp
    <title>IM-@yield('title', 'Page')@if($documentOrgName !== '') - {{ $documentOrgName }}@endif</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.ico') }}">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/dist/css/adminlte.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ theme_asset('admin-custom.css') }}"> --}}
    <link rel="stylesheet" href="{{ theme_asset('custom-datatable.css') }}">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ theme_asset('dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('select.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('responsive.bootstrap5.min.css') }}">


  <link rel="stylesheet" href="{{ theme_asset('select2.min.css') }}">
  <link rel="stylesheet" href="{{ theme_asset('admin-custom.css') }}">

    <link rel="stylesheet" href="{{ theme_asset('select2-bootstrap4.min.css') }}">
    <!-- Flatpickr (datepicker) -->
    <link rel="stylesheet" href="{{ theme_asset('flatpickr.min.css') }}">
    <!-- Quill editor -->
    <link rel="stylesheet" href="{{ theme_asset('quill.snow.css') }}">

     @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Your Custom CSS (optional) -->
    @stack('styles')
    @livewireStyles


    <!-- In masterapp/layouts/app.blade.php, inside the <head> tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <!-- Livewire global loader: transparent overlay + spinner -->
    <div id="livewire-global-loader" class="livewire-global-loader livewire-global-loader--hidden" aria-hidden="true">
        <div class="livewire-global-loader__spinner"></div>
    </div>

    <div class="wrapper">
        <!-- ============================================================= -->
        <!-- PRELOADER - OPTIONAL -->
        <!-- ============================================================= -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- CUSTOM HEADER -->
        <!-- ============================================================= -->
         @include('masterapp.partials.top-menu')
        {{-- @include('masterapp.partials.notifications') --}}

        <!--  ============================================================= -->
        <!-- CUSTOM LEFT PANEL (SIDEBAR) -->
        <!-- ============================================================= -->
         @include('masterapp.partials.sidebar-panel')

        <!-- ============================================================= -->
        <!-- CONTENT WRAPPER -->
        <!-- ============================================================= -->
        <div class="content-wrapper">
            <!-- Content Header (Page header)    -->

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">Seemanshu Industries</a>.</strong>
            All rights reserved.
        </footer>
    </div>
    <div class="modal fade" id="ruhiGlobalPrintPreviewModal" tabindex="-1" role="dialog" aria-hidden="true" style="background: rgba(0,0,0,0.65);">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 92vw;">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Print Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-0" style="height: 78vh;">
                    <iframe id="ruhiGlobalPrintPreviewFrame" title="Print preview" style="width:100%; height:100%; border:0;"></iframe>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->




    <!-- AdminLTE JS -->
    <!-- <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script> -->

    <!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js') }}"></script>
    <script>
      // When the sidebar search close (X) button is clicked, clear the search input (AdminLTE only closes the panel).
      // Use capture phase so we run before AdminLTE removes sidebar-search-open.
      document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('[data-widget="sidebar-search"] .btn');
        if (!btn) return;
        var isCloseButton = btn.querySelector && btn.querySelector('.fa-times');
        var formInline = btn.closest('.form-inline');
        var isOpen = formInline && formInline.classList.contains('sidebar-search-open');
        if (isCloseButton || isOpen) {
          var input = document.querySelector('[data-widget="sidebar-search"] .form-control');
          if (input) input.value = '';
        }
      }, true);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <!-- DataTables Core -->
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap5.min.js') }}"></script>

    <!-- DataTables Extensions -->
    <script src="{{ asset('js/dataTables.select.min.js') }}"></script>

    <!-- Responsive -->
    <script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/responsive.bootstrap5.min.js') }}"></script>

    <script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('js/jszip.min.js') }}"></script>
    <script src="{{ asset('js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('js/vfs_fonts.js') }}"></script>
    <!-- Print button (after other DataTables/Buttons so search-box fix is not overwritten) -->
    <script src="{{ asset('js/buttons.print.js') }}"></script>


    <script src="{{ asset('js/settings-panel.js') }}"></script>

    <!-- SweetAlert -->
    <script src="{{ asset('js/sweetalert2.js') }}"></script>

    <!-- Quill stylesheet: theme bundle (head) -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <!-- Your Custom JS (optional) -->
    <!-- Column Toggle Script -->
    <script src="{{ asset('js/column-toggle.js') }}"></script>

    <script src="{{ asset('js/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/jquery-validation/additional-methods.min.js') }}"></script>

    <script src="{{ asset('js/generic-notification-helper.js') }}"></script>
    <script src="{{ asset('js/generic-model-form.js') }}"></script>
    <script src="{{ asset('js/permission-checkboxes.js') }}"></script>
    <script src="{{ asset('js/generic-datatable.js') }}"></script>
    <script src="{{ asset('js/ajax-form-handler.js') }}"></script>
    <script src="{{ asset('js/generic-delete-handler.js') }}"></script>
    <script src="{{ asset('js/masterapp/master-data-livewire.js') }}"></script>
    <script src="{{ asset('js/masterapp/ruhi-master-select2.js') }}"></script>
    <script src="{{ asset('js/users-create.js') }}"></script>
    <!-- Flatpickr (datepicker) -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

@stack('scripts')
@livewireScripts
<script>
document.addEventListener('livewire:init', function () {
    var loader = document.getElementById('livewire-global-loader');
    if (!loader || typeof Livewire === 'undefined') return;
    Livewire.interceptRequest(function (_ref) {
        var onSend = _ref.onSend, onFinish = _ref.onFinish;
        onSend(function () {
            loader.classList.remove('livewire-global-loader--hidden');
            loader.setAttribute('aria-hidden', 'false');
        });
        onFinish(function () {
            loader.classList.add('livewire-global-loader--hidden');
            loader.setAttribute('aria-hidden', 'true');
        });
    });
});
</script>
<script>
document.addEventListener('click', function (event) {
    var link = event.target.closest && event.target.closest('a.ruhi-print-preview-link');
    if (!link) return;
    event.preventDefault();

    var href = link.getAttribute('href');
    if (!href) return;

    var loader = document.getElementById('livewire-global-loader');
    if (loader) {
        loader.classList.remove('livewire-global-loader--hidden');
        loader.setAttribute('aria-hidden', 'false');
    }

    var frame = document.getElementById('ruhiGlobalPrintPreviewFrame');
    if (!frame) {
        // If preview frame is unavailable, open in new tab instead of replacing current page.
        window.open(href, '_blank', 'noopener');
        if (loader) {
            loader.classList.add('livewire-global-loader--hidden');
            loader.setAttribute('aria-hidden', 'true');
        }
        return;
    }

    var printed = false;
    var fallbackTimer = null;

    function hideLoader() {
        if (loader) {
            loader.classList.add('livewire-global-loader--hidden');
            loader.setAttribute('aria-hidden', 'true');
        }
    }

    frame.onload = function () {
        if (printed) return;
        try {
            var win = frame.contentWindow;
            if (win) {
                printed = true;
                if (fallbackTimer) clearTimeout(fallbackTimer);
                win.focus();
                win.print();
                // Keep iframe content intact; clearing too early causes blank print pages.
                hideLoader();
            }
        } catch (e) {
            // Do not navigate current page on print errors.
            // Keep user on report screen and allow retry.
            hideLoader();
        }
    };
    fallbackTimer = setTimeout(function () {
        if (printed) return;
        // Timeout fallback: keep user on same page (no same-tab redirect).
        hideLoader();
    }, 8000);
    frame.setAttribute('src', href);
});
</script>
{{-- @if (session('success'))
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: @json(session('success')),
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: @json(session('error')),
        showConfirmButton: false,
        timer: 3000
    });
</script>
@endif --}}

</body>
</html>
