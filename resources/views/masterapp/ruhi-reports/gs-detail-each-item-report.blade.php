@extends('masterapp.layouts.app')

@section('title', 'GS Wise Detail Report of Each Item')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-10">
                    <h1 class="m-0 font-weight-bold">GS Wise Detail Report of Each Item</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-detail-each-item-report />
        </div>
    </section>
@endsection
