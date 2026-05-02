@extends('masterapp.layouts.app')

@section('title', 'Manage Collet K-Stones')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0">Manage Collet K-Stones</h1>
                    <p class="text-muted mb-0 small">Item: <strong>{{ $product->product_name }}</strong> (ID {{ $product->id }})</p>
                </div>
                <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                    <a href="{{ route('masterapp.ruhi-items.index') }}" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fa fa-arrow-left mr-1"></i> Back to items
                    </a>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        onclick="document.getElementById('ruhiColletKstoneAddTrigger')?.click();"
                    >
                        <i class="fas fa-plus mr-1"></i> Add K-Stone
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-item-collet-kstones-list :product-id="$product->id" />
        </div>
    </section>
@endsection
