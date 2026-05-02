@extends('masterapp.layouts.app')

@section('title', 'List GS items')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 class="m-0">List GS "{{ $gs->name }}" items</h1>
                </div>
                <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                    <a href="{{ route('masterapp.ruhi-gs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <livewire:master-app.ruhi-gs-lots-list :gs-id="$gs->id" />
        </div>
    </section>
@endsection
