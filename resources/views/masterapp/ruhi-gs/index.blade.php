@extends('masterapp.layouts.app')

@section('title', 'Manage GS')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0">Manage GS</h1>
                </div>
                <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        onclick="document.getElementById('ruhiAddGsTrigger').click();"
                    >
                        <i class="fas fa-plus mr-1"></i> Add GS
                    </button>
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

