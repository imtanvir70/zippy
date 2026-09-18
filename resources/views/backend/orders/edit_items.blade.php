@extends('backend.layouts.app')

@section('title', 'Edit Items - ' . ($type === 'order' ? 'Order #' . $order->order_number : 'Abandoned Cart'))

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="h3 mb-1 fw-bold text-dark">Edit Items</h4>
            <div class="text-muted small">
                @if($type === 'order')
                    Modifying items for Order <strong class="text-dark">#{{ $order->order_number }}</strong>
                @else
                    Modifying items for Abandoned Cart <strong class="text-dark">#{{ $cart->id }}</strong>
                @endif
            </div>
        </div>
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4 border-0 rounded-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Current Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="itemsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Price</th>
                                    <th style="width: 130px;">Quantity</th>
                                    <th>Total</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items injected by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light p-4 border-top">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-grow-1">
                            <select id="productSearch" class="form-select"></select>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="addProductFromSelect()">
                            <i class="fa-solid fa-plus"></i> Add Product
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-calculator text-primary me-2"></i> Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <strong class="text-dark">৳ <span id="summarySubtotal">0</span></strong>
                    </div>
                    @if($discount > 0)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount</span>
                        <strong>- ৳ <span id="summaryDiscount">{{ $discount }}</span></strong>
                    </div>
                    @endif
                    @if($shipping > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <strong class="text-dark">+ ৳ <span id="summaryShipping">{{ $shipping }}</span></strong>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between fs-5 mb-4">
                        <span class="fw-bold">Total</span>
                        <strong class="text-dark fw-bold">৳ <span id="summaryTotal">0</span></strong>
                    </div>

                    <form id="saveItemsForm" action="{{ $submitUrl }}" method="POST">
                        @csrf
                        <input type="hidden" name="items_json" id="itemsJsonInput" value="">
                        <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" onclick="saveChanges()">
                            <i class="fa-solid fa-save me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let items = @json($items);
    const discount = {{ $discount }};
    const shipping = {{ $shipping }};
    const placeholderImg = '{{ asset("images/product-placeholder.svg") }}';

    function renderItems() {
        const tbody = document.querySelector('#itemsTable tbody');
        tbody.innerHTML = '';
        
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">No items in this order. Add some below.</td></tr>`;
        }

        let subtotal = 0;

        items.forEach((item, index) => {
            const itemTotal = item.quantity * item.unit_price;
            subtotal += itemTotal;
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="${item.product_image || placeholderImg}" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='${placeholderImg}'">
                        <div>
                            <div class="fw-bold small text-truncate" style="max-width: 250px;">${item.product_title}</div>
                            <small class="text-muted">ID: #${item.product_id}</small>
                        </div>
                    </div>
                </td>
                <td>৳ ${item.unit_price.toLocaleString('en-US')}</td>
                <td>
                    <div class="input-group input-group-sm" style="width: 110px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(${index}, -1)">-</button>
                        <input type="number" class="form-control text-center" value="${item.quantity}" min="1" onchange="updateQty(${index}, this.value)">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="fw-bold">৳ ${itemTotal.toLocaleString('en-US')}</td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-light text-danger border" onclick="removeItem(${index})" title="Remove"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('summarySubtotal').innerText = subtotal.toLocaleString('en-US');
        let total = subtotal - discount + shipping;
        if(total < 0) total = 0;
        document.getElementById('summaryTotal').innerText = total.toLocaleString('en-US');
    }

    function changeQty(index, delta) {
        items[index].quantity += delta;
        if(items[index].quantity < 1) items[index].quantity = 1;
        renderItems();
    }

    function updateQty(index, val) {
        let q = parseInt(val);
        if(isNaN(q) || q < 1) q = 1;
        items[index].quantity = q;
        renderItems();
    }

    function removeItem(index) {
        if(confirm('Are you sure you want to remove this item?')) {
            items.splice(index, 1);
            renderItems();
        }
    }

    $(document).ready(function() {
        renderItems();

        $('#productSearch').select2({
            placeholder: 'Search for a product by name or SKU...',
            ajax: {
                url: '{{ route("admin.orders.search_products") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            },
            templateResult: formatProduct,
            templateSelection: formatProductSelection
        });
    });

    function formatProduct(repo) {
        if (repo.loading) return repo.text;
        var $container = $(
            "<div class='d-flex align-items-center gap-2'>" +
                "<img src='" + (repo.image || placeholderImg) + "' style='width:30px; height:30px; object-fit:cover; border-radius:4px;' onerror=\"this.src='"+placeholderImg+"'\" />" +
                "<div>" +
                    "<div class='small fw-bold'>" + repo.text + "</div>" +
                    "<div class='text-muted' style='font-size:10px;'>৳" + repo.price + (repo.sku ? " | SKU: " + repo.sku : "") + "</div>" +
                "</div>" +
            "</div>"
        );
        return $container;
    }

    function formatProductSelection(repo) {
        return repo.text || repo.id;
    }

    function addProductFromSelect() {
        const data = $('#productSearch').select2('data');
        if(!data || data.length === 0 || !data[0].id) {
            alert('Please select a product first.');
            return;
        }
        const prod = data[0];
        
        // check if already exists
        const existing = items.find(i => i.product_id == prod.id);
        if(existing) {
            existing.quantity++;
        } else {
            items.push({
                product_id: prod.id,
                product_title: prod.text,
                product_image: prod.image,
                unit_price: parseFloat(prod.price) || 0,
                quantity: 1
            });
        }
        
        $('#productSearch').val(null).trigger('change');
        renderItems();
    }

    function saveChanges() {
        if(items.length === 0) {
            if(!confirm('You have no items. This might cause issues. Proceed anyway?')) return;
        }
        
        document.getElementById('itemsJsonInput').value = JSON.stringify(items);
        
        const btn = document.querySelector('#saveItemsForm button');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        
        document.getElementById('saveItemsForm').submit();
    }
</script>
@endpush

