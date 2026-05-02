@extends('masterapp.layouts.app')

@section('title', 'GS Die Report')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0 font-weight-bold">GS Die Report</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-die-report />
        </div>
    </section>
@endsection
