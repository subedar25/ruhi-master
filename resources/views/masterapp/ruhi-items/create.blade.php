@extends('masterapp.layouts.app')

@section('title', 'Add Item')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Add Item</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('masterapp.ruhi-items.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('masterapp.ruhi-items.form')
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

