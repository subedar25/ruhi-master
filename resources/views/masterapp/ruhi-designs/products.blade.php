@extends('masterapp.layouts.app')

@section('title', 'List Design Items for Design (' . $design->design_name . ')')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0">List Design Items for Design ({{ $design->design_name }})</h1>
                </div>
                <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                    <a href="{{ route('masterapp.ruhi-designs.products.print', $design->id) }}" class="btn btn-outline-primary btn-sm mr-1 ruhi-print-preview-link">
                        <i class="fa fa-print mr-1"></i> Print
                    </a>
                    <a href="{{ route('masterapp.ruhi-designs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-arrow-left mr-1"></i> Back to designs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-design-products :design-id="$design->id" />
        </div>
    </section>
@endsection
