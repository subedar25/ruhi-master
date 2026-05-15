@extends('masterapp.layouts.app')

@section('title', 'Select Organization')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <h1 class="m-0 text-dark">Select organization</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 55vh;">
        <div class="modal-dialog m-0" role="document" style="max-width: 28rem; width: 100%;">
            <div class="modal-content shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Choose an organization</h5>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3">Your account is linked to more than one organization. Pick one to continue.</p>
                    <div class="list-group">
                        @foreach ($organizations as $org)
                            <form method="POST" action="{{ route('masterapp.organization.switch') }}" class="m-0">
                                @csrf
                                <input type="hidden" name="organization_id" value="{{ $org->id }}">
                                <button type="submit" class="list-group-item list-group-item-action list-group-item-primary d-flex justify-content-between align-items-center">
                                    <span class="text-truncate" style="max-width: 100%;">{{ $org->name }}</span>
                                    <i class="fas fa-chevron-right small"></i>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
