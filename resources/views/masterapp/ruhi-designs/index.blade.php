@extends('masterapp.layouts.app')

@section('title', 'Manage Design')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <h1 class="m-0 text-dark">Manage Design</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <livewire:master-app.ruhi-designs />
    </div>
</section>
@endsection

