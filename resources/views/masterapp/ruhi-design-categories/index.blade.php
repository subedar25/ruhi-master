@extends('masterapp.layouts.app')

@section('title', 'Design Category')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h1 class="m-0">Design Category</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-design-categories-list />
        </div>
    </section>
@endsection
