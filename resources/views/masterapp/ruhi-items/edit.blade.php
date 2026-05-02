@extends('masterapp.layouts.app')

@section('title', 'Edit Item')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Edit Item</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('masterapp.ruhi-items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('masterapp.ruhi-items.form', ['item' => $item])
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

