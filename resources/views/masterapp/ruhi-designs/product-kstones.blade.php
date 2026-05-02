@extends('masterapp.layouts.app')

@section('title', 'List KStone')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0">List KStone - ({{ $product->product_name }})</h1>
                    <p class="text-muted mb-0 small">List K-Stone</p>
                </div>
                <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                    <a href="{{ route('masterapp.ruhi-designs.products.kstones.print', ['design' => $design->id, 'product' => $product->id]) }}" target="_blank" class="btn btn-outline-primary btn-sm mr-1">
                        <i class="fa fa-print mr-1"></i> Print
                    </a>
                    <a href="{{ route('masterapp.ruhi-designs.products', $design->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-design-product-item-kstones-list :design-id="$design->id" :product-id="$product->id" />
        </div>
    </section>
@endsection
