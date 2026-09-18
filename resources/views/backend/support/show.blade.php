@extends('backend.layouts.app')

@section('title', 'Ticket Details')

@section('content')
<!-- 1. Header Card -->
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Ticket #{{ $ticket->ticket_number }}</h3>
            <small class="text-muted">{{ $ticket->subject }}</small>
        </div>
        <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Desk
        </a>
    </div>
</div>

<!-- 2. Content Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                <div>
                    <h6 class="fw-bold mb-1">{{ $ticket->customer_name }}</h6>
                    <div class="text-primary fw-bold">{{ $ticket->customer_phone }}</div>
                    <small class="text-muted">{{ $ticket->customer_email }}</small>
                </div>
                <span class="badge bg-primary-subtle text-primary">{{ ucfirst($ticket->status) }}</span>
            </div>
            <h6 class="fw-bold">{{ $ticket->subject }}</h6>
            <div class="text-secondary p-3 rounded-3 border bg-light mb-3">
                {{ $ticket->message }}
            </div>
            @if($ticket->admin_reply)
                <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle">
                    <span class="fw-bold text-success d-block mb-1"><i class="fa-solid fa-reply me-1"></i> Admin Reply:</span>
                    <div>{{ $ticket->admin_reply }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card p-3 p-md-4 h-100">
            <h6 class="fw-bold mb-3 pb-2 border-bottom">
                <i class="fa-solid fa-paper-plane text-primary me-2"></i> Post Admin Reply
            </h6>
            <form action="{{ route('admin.support.reply', $ticket->id) }}" method="POST" class="d-flex flex-column gap-3">
                @csrf
                <div>
                    <label class="form-label">Update Status</label>
                    <select name="status" class="form-select">
                        <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Keep Open</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Reply Message <span class="text-danger">*</span></label>
                    <textarea name="admin_reply" rows="5" class="form-control" placeholder="Write response to customer..." required>{{ $ticket->admin_reply }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary py-2.5 fw-bold">
                    <i class="fa-solid fa-paper-plane me-1"></i> Submit Reply
                </button>
            </form>
        </div>
    </div>
</div>
@endsection