@extends('masterapp.layouts.app')

@section('title', 'Manage Item')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Manage Item</h1>
            </div>
            <div class="col-sm-6 d-flex justify-content-end">
                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="document.getElementById('ruhiAddItemTrigger')?.click();"
                >
                    <i class="fa fa-plus"></i> Add Item
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <livewire:master-app.ruhi-items />
    </div>
</section>
@endsection

