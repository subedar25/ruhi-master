@extends('masterapp.layouts.app')

@section('title', 'GS Wise Drop Report')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-10">
                    <h1 class="m-0 font-weight-bold d-flex align-items-center">
                        <i class="fas fa-tint text-info mr-2"></i>
                        GS Wise Drop Report
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-wise-drop-report />
        </div>
    </section>
@endsection
