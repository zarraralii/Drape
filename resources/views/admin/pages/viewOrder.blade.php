@include('admin.layouts.header', ['title' => 'Order Details'])
@include('admin.layouts.menu')

@php($order = $data['order'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            @if (!$order)
                <div class="alert alert-warning">Order not found.</div>
            @else
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">Order #{{ $order->uid ?? $order->id }}</h4>
                                <span class="badge bg-primary">{{ $order->status ?? '—' }}</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                                <th>Discount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($order->items as $item)
                                                <tr>
                                                    <td>{{ $item->display_name }}@if ($item->variant_label)<br><small class="text-muted">{{ $item->variant_label }}@if ($item->sku) · {{ $item->sku }}@endif</small>@endif</td>
                                                    <td>{{ $item->qty }}</td>
                                                    <td>{{ money($item->item_total) }}</td>
                                                    <td>{{ money($item->discount) }}</td>
                                                    <td>{{ $item->status ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted">No items.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <h5 class="mt-4">Update Status</h5>
                                <p class="text-muted small">Cancelling puts the items back in stock; reopening a cancelled order takes them out again.</p>
                                <form id="statusForm" class="d-flex gap-2">
                                    <select name="status" id="statusSelect" class="form-select w-auto">
                                        @foreach (\App\Models\Order::STATUSES as $status)
                                            <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>

                                @if ($order->trails->isNotEmpty())
                                    <h5 class="mt-4">History</h5>
                                    <ul class="list-unstyled">
                                        @foreach ($order->trails as $trail)
                                            <li class="border-bottom py-1"><strong>{{ $trail->status }}</strong> — {{ $trail->updated_by }} <span class="text-muted small">({{ $trail->created_at->diffForHumans() }})</span></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Customer</h5></div>
                            <div class="card-body">
                                <p class="mb-1"><strong>{{ $order->contact_name ?? 'Guest' }}</strong> @if (!$order->user_id)<span class="badge bg-light text-muted">Guest checkout</span>@endif</p>
                                <p class="mb-1 text-muted">{{ $order->contact_email ?? '—' }}</p>
                                <p class="mb-0 text-muted">{{ $order->contact_phone ?? '—' }}</p>@if ($order->remarks)<p class="mt-2 mb-0 small"><strong>Note:</strong> {{ $order->remarks }}</p>@endif
                            </div>
                        </div>

                        @if ($order->agent)
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">Referred By</h5></div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>{{ $order->agent->name }}</strong> <span class="badge bg-{{ $order->agent->type === 'vendor' ? 'primary' : 'info' }}">{{ ucfirst($order->agent->type) }}</span></p>
                                    <p class="mb-0 text-muted">Code: <code>{{ $order->agent->referral_code }}</code></p>
                                </div>
                            </div>
                        @endif

                        @if ($order->delivery)
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">Delivery Address</h5></div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $order->delivery->full_address }}</p>
                                    <p class="mb-0 text-muted">{{ $order->delivery->city }}, {{ $order->delivery->state }} {{ $order->delivery->postal_code }}</p>
                                    <p class="mb-0 text-muted">{{ $order->delivery->country }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Summary</h5></div>
                            <div class="card-body">
                                <p class="d-flex justify-content-between"><span>Subtotal</span> <span>{{ money($order->total_amount + ($order->discount_amount ?? 0) - ($order->delivery_charges ?? 0)) }}</span></p>
                                @if ($order->coupon_code)
                                    <p class="d-flex justify-content-between text-success"><span>Coupon <code>{{ $order->coupon_code }}</code></span> <span>-{{ money(($order->discount_amount ?? 0) - ($order->admin_discount ?? 0)) }}</span></p>
                                @endif
                                @if ($order->admin_discount > 0)
                                    <p class="d-flex justify-content-between text-success">
                                        <span>Admin Discount{{ $order->admin_discount_type === 'percent' ? ' (' . (float) $order->admin_discount_value . '%)' : '' }}</span>
                                        <span>-{{ money($order->admin_discount) }}</span>
                                    </p>
                                @endif
                                <p class="d-flex justify-content-between"><span>Delivery</span> <span>{{ money($order->delivery_charges) }}</span></p>
                                <p class="d-flex justify-content-between"><span>Total</span> <strong>{{ money($order->total_amount) }}</strong></p>
                                <p class="d-flex justify-content-between mb-0"><span>Payment</span> <span>{{ $order->payment_method ?? '—' }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('admin.layouts.footer')

<script>
    document.getElementById('statusForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        $.post("{{ route('admin.order.update-status') }}", {
            _token: '{{ csrf_token() }}',
            order_id: {{ $order->id ?? 'null' }},
            status: document.getElementById('statusSelect').value,
        }).done(function (res) {
            if (res.success) {
                Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
            }
        }).fail(function (xhr) {
            // e.g. reopening a cancelled order whose items have sold out since.
            Swal.fire({ icon: 'error', title: 'Status not changed', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Please try again.' });
        });
    });
</script>
