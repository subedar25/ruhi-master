@extends('masterapp.layouts.app')

@section('title', 'GS Wise Casting Report')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0 font-weight-bold">GS Wise Casting Report</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-wise-casting-report />
        </div>
    </section>
@endsection
