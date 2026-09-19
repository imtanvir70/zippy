@extends('backend.layouts.app')

@section('title', 'Order Bumps & Upsells')

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon" style="width: 48px; height: 48px; border-radius: 14px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="fa-solid fa-gift"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">Checkout Order Bumps</h3>
                <small class="text-muted">চেকআউট পেজে কাস্টমারদের আকর্ষণীয় ১-ক্লিক অ্যাড-অন প্রোডাক্ট অফার দিন।</small>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-primary px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createBumpModal">
                <i class="fa-solid fa-plus"></i>
                <span>নতুন অর্ডার বাম্প তৈরি করুন</span>
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">অফার শিরোনাম</th>
                    <th>টার্গেট প্রোডাক্ট</th>
                    <th>অ্যাড-অন (বাম্ব) প্রোডাক্ট</th>
                    <th>অফার মূল্য</th>
                    <th>স্ট্যাটাস</th>
                    <th class="text-end pe-4">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bumps as $b)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $b->title }}</div>
                            <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">{{ $b->description }}</small>
                        </td>
                        <td>
                            @if(!empty($b->primary_product_title))
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">{{ $b->primary_product_title }}</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">সকল প্রোডাক্টে (Global)</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if(!empty($b->bump_image))
                                    <img src="{{ product_image_url($b->bump_image) }}" alt="" class="rounded border object-fit-cover" style="width: 38px; height: 38px;" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                                @endif
                                <div>
                                    <div class="fw-semibold text-dark">{{ $b->bump_product_title }}</div>
                                    <small class="text-muted text-decoration-line-through">নিয়মিত: ৳ {{ number_format($b->bump_original_price, 0) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold text-success font-monospace fs-6">৳ {{ number_format($b->price, 0) }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.bumps.toggle', $b->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="badge border-0 {{ $b->is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} rounded-pill px-3 py-1.5 fw-semibold cursor-pointer">
                                    {{ $b->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end pe-4">
                            <form action="{{ route('admin.bumps.destroy', $b->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত এই অফারটি মুছে ফেলতে চান?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-gift fs-1 mb-2 text-secondary opacity-50 d-block"></i>
                            কোনো সক্রিয় অর্ডার বাম্প পাওয়া যায়নি।
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bumps->hasPages())
        <div class="p-3 border-top">
            {{ $bumps->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="createBumpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.bumps.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-gift text-primary me-2"></i>নতুন অর্ডার বাম্প তৈরি করুন</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label fw-semibold">টার্গেট প্রোডাক্ট (ঐচ্ছিক)</label>
                        <select name="product_id" class="form-select">
                            <option value="">-- সকল প্রোডাক্টে প্রদর্শন করুন (Global) --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} (৳ {{ number_format($p->price, 0) }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">নির্দিষ্ট কোনো প্রোডাক্ট নির্বাচন না করলে কার্টে যেকোনো আইটেম থাকলে এই অফার শো করবে।</div>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">অ্যাড-অন (বাম্ব) প্রোডাক্ট <span class="text-danger">*</span></label>
                        <select name="bump_product_id" class="form-select" required>
                            <option value="">-- যে প্রোডাক্টটি অফারে দিতে চান --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} (নিয়মিত দাম: ৳ {{ number_format($p->price, 0) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">আকর্ষণীয় অফার শিরোনাম <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="যেমন: বিশেষ অফার: মাত্র ৯৯৯ টাকায় প্রিমিয়াম ইয়ারফোন!" required>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">সংক্ষিপ্ত বিবরণ</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="অফারের সংক্ষিপ্ত সুবিধা ও বিবরণ..."></textarea>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">অফার মূল্য (BDT) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control font-monospace" placeholder="999.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

