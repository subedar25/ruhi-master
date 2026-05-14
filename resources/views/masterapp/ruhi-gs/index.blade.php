@extends('masterapp.layouts.app')

@section('title', 'Manage GS')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h1 class="m-0 text-dark">Manage GS</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-list />
        </div>
    </section>
@endsection

