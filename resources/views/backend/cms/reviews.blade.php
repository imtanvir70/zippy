@extends('backend.layouts.app')

@section('title', 'Product Reviews Management')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Customer Reviews Management</h3>
            <p class="text-muted small mb-0">Approve, reject, or moderate reviews submitted by customers.</p>
        </div>
    </div>
</div>

<div class="card p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted">
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review Feedback</th>
                    <th>Photos</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $rev)
                @php
                    $revImages = [];
                    if (!empty($rev->images)) {
                        $decoded = is_string($rev->images) ? json_decode($rev->images, true) : $rev->images;
                        if (is_array($decoded)) {
                            $revImages = $decoded;
                        }
                    }
                    if (empty($revImages) && !empty($rev->photo)) {
                        $revImages = [$rev->photo];
                    }
                @endphp
                <tr>
                    <td class="fw-bold text-dark">{{ $rev->product_title ?? 'Product Removed' }}</td>
                    <td>
                        <div class="fw-semibold">{{ $rev->customer_name }}</div>
                        <small class="text-muted">{{ $rev->customer_phone ?? 'No Phone' }}</small>
                    </td>
                    <td>
                        <span class="text-warning fw-bold">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-{{ $i <= $rev->rating ? 'solid' : 'regular' }} fa-star"></i>
                            @endfor
                        </span>
                    </td>
                    <td>
                        <p class="small mb-0 text-secondary" style="max-width: 280px;">{{ $rev->comment ?: 'No written comment.' }}</p>
                    </td>
                    <td>
                        @if(!empty($revImages))
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($revImages as $img)
                                    <a href="{{ asset($img) }}" target="_blank" class="d-inline-block">
                                        <img src="{{ asset($img) }}" class="rounded border shadow-2xs" style="width: 44px; height: 44px; object-fit: cover;" alt="Review Photo">
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">None</span>
                        @endif
                    </td>
                    <td>
                        @if($rev->status === 'approved')
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Approved</span>
                        @elseif($rev->status === 'rejected')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Rejected</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            @if($rev->status !== 'approved')
                                <button type="button" class="btn btn-sm btn-success" onclick="toggleStatus({{ $rev->id }}, 'approved')" title="Approve Review">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            @endif
                            @if($rev->status !== 'rejected')
                                <button type="button" class="btn btn-sm btn-warning text-dark" onclick="toggleStatus({{ $rev->id }}, 'rejected')" title="Reject Review">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            @endif
                            <form action="{{ route('admin.reviews.delete', $rev->id) }}" method="POST" onsubmit="return confirm('Delete this review permanently?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No customer reviews submitted yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $reviews->links() }}
    </div>
</div>

<script>
(() => {
    function toggleStatus(reviewId, status) {
        axios.post('/admin/reviews/' + reviewId + '/toggle', { status: status })
        .then(res => {
            const data = res.data;
            if (data.success) {
                if (window.Turbo) {
                    window.Turbo.visit(window.location.href, { action: 'replace' });
                } else {
                    window.location.reload();
                }
            } else {
                if (window.showToast) window.showToast('Failed to update review status.', 'error');
            }
        })
        .catch(err => {
            if (window.showToast) window.showToast('Network error.', 'error');
        });
    }

    window.toggleStatus = toggleStatus;
})();
</script>
@endsection

