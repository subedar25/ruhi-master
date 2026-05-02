@extends('masterapp.layouts.app')

@section('title', 'GS Wise Collet Kstone Color Report')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-10">
                    <h1 class="m-0 font-weight-bold d-flex align-items-center">
                        <i class="fas fa-gem text-info mr-2"></i>
                        GS Wise Collet Kstone Color Report
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-collet-kstone-color-report />
        </div>
    </section>
@endsection
