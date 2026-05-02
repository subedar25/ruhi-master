@extends('masterapp.layouts.app')

@section('title', 'GS Color Full Report')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-10">
                    <h1 class="m-0 font-weight-bold d-flex align-items-center">
                        <i class="fas fa-fill-drip text-primary mr-2"></i>
                        GS Color Full Report
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-color-full-report />
        </div>
    </section>
@endsection
