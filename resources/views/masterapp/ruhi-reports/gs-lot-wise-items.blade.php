@extends('masterapp.layouts.app')

@section('title', 'GS Wise Lot Wise item report')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0">GS Wise Lot Wise item report</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-lot-wise-items-report />
        </div>
    </section>
@endsection
