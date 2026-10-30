@include('admin.layouts.header', ['title' => 'Create Order'])
@include('admin.layouts.menu')

@php($oldItems = old('items', [['product_id' => '', 'qty' => 1]]))

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <form action="{{ route('admin.create-order') }}" method="POST" id="createOrderForm">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Products</h4>
                                <button type="button" id="addItemBtn" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-plus"></i> Add Product</button>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th style="width: 110px;">Qty</th>
                                            <th style="width: 140px;">Unit Price</th>
                                            <th style="width: 140px;">Line Total</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @foreach ($oldItems as $i => $row)
                                            <tr class="item-row">
                                                <td>
                                                    <select name="items[{{ $i }}][product_id]" class="form-select product-select" required>
                                                        <option value="">Select product</option>
                                                        @foreach ($products as $product)
                                                            @if ($product->isVariable())
                                                                {{-- One choice per size/colour: value "productId:variantId" --}}
                                                                <optgroup label="{{ $product->name }}">
                                                                    @foreach ($product->variations as $variant)
                                                                        @php($key = $product->id . ':' . $variant->id)
                                                                        <option value="{{ $key }}" data-price="{{ $variant->unitPrice($product) }}" data-stock="{{ $variant->quantity }}" {{ (string) ($row['product_id'] ?? '') === $key ? 'selected' : '' }} @disabled($variant->quantity <= 0)>
                                                                            {{ $product->name }} — {{ $variant->label }} (Stock: {{ $variant->quantity }})
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @else
                                                                <option value="{{ $product->id }}" data-price="{{ (float) $product->current_price }}" data-stock="{{ $product->qty }}" {{ (string) ($row['product_id'] ?? '') === (string) $product->id ? 'selected' : '' }}>
                                                                    {{ $product->name }} (Stock: {{ $product->qty ?? '∞' }})
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" name="items[{{ $i }}][qty]" class="form-control qty-input" min="1" value="{{ $row['qty'] ?? 1 }}" required></td>
                                                <td class="unit-price">—</td>
                                                <td class="line-total">—</td>
                                                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="mdi mdi-close"></i></button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h4 class="card-title mb-0">Customer &amp; Delivery</h4></div>
                            <div class="card-body">
                                <p class="text-muted small">If a customer with this phone number already exists, the order is attached to their account.</p>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Optional">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Address Line 1</label>
                                        <input type="text" name="address_line_1" class="form-control" value="{{ old('address_line_1') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Address Line 2</label>
                                        <input type="text" name="address_line_2" class="form-control" value="{{ old('address_line_2') }}" placeholder="Optional">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" value="{{ old('city') }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">State / Province</label>
                                        <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Country</label>
                                        <input type="text" name="country" class="form-control" value="{{ old('country', 'Pakistan') }}" required>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title mb-0">Order Details</h4></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" {{ old('status', 'Pending') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select" required>
                                        @foreach (['Cash on Delivery', 'Bank Transfer', 'Cash', 'Card'] as $method)
                                            <option value="{{ $method }}" {{ old('payment_method', 'Cash on Delivery') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Agent</label>
                                    <select name="agent_id" class="form-select">
                                        <option value="">None</option>
                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ (string) old('agent_id') === (string) $agent->id ? 'selected' : '' }}>{{ $agent->name }} ({{ (float) $agent->commission_rate }}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Coupon</label>
                                    <select name="coupon_code" id="couponSelect" class="form-select">
                                        <option value="">None</option>
                                        @foreach ($coupons as $coupon)
                                            <option value="{{ $coupon->code }}" data-type="{{ $coupon->type }}" data-value="{{ (float) $coupon->value }}" data-min="{{ $coupon->min_order_amount !== null ? (float) $coupon->min_order_amount : '' }}" {{ old('coupon_code') === $coupon->code ? 'selected' : '' }}>
                                                {{ $coupon->code }} ({{ $coupon->type === 'percent' ? (float) $coupon->value . '%' : money($coupon->value) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Discount</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" name="admin_discount_value" id="adminDiscountValue" class="form-control" value="{{ old('admin_discount_value') }}" placeholder="0">
                                        <select name="admin_discount_type" id="adminDiscountType" class="form-select" style="max-width: 110px;">
                                            <option value="fixed" {{ old('admin_discount_type', 'fixed') === 'fixed' ? 'selected' : '' }}>{{ \App\Support\Store::currency() }}</option>
                                            <option value="percent" {{ old('admin_discount_type') === 'percent' ? 'selected' : '' }}>%</option>
                                        </select>
                                    </div>
                                    <small class="text-muted">Your own discount for this order. Applied after the coupon (if any).</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Delivery Charges ({{ \App\Support\Store::currency() }})</label>
                                    <input type="number" step="0.01" min="0" name="delivery_charges" id="deliveryInput" class="form-control" value="{{ old('delivery_charges', $defaultDeliveryCharge) }}">
                                    <small class="text-muted">Defaults to the Site Info delivery charge; you can override it for this order.</small>
                                </div>

                                <hr>
                                <p class="d-flex justify-content-between mb-1"><span>Subtotal</span> <span id="sumSubtotal">{{ \App\Support\Store::currency() }} 0</span></p>
                                <p class="d-flex justify-content-between mb-1 text-success"><span>Coupon</span> <span id="sumDiscount">-{{ \App\Support\Store::currency() }} 0</span></p>
                                <p class="d-flex justify-content-between mb-1 text-success d-none" id="sumAdminDiscountRow"><span>Discount</span> <span id="sumAdminDiscount">-{{ \App\Support\Store::currency() }} 0</span></p>
                                <p class="d-flex justify-content-between mb-1"><span>Delivery</span> <span id="sumDelivery">{{ \App\Support\Store::currency() }} 0</span></p>
                                <p class="d-flex justify-content-between mb-3"><strong>Total</strong> <strong id="sumTotal">{{ \App\Support\Store::currency() }} 0</strong></p>
                                <p class="text-danger small d-none" id="couponWarning"></p>

                                <button type="submit" class="btn btn-primary w-100">Create Order</button>
                                <a href="{{ route('admin.orders') }}" class="btn btn-light w-100 mt-2">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.layouts.footer')

<script>
    $(document).ready(function () {
        // Totals here are a preview only — the server recalculates prices,
        // coupon discount and stock limits from the database on submit.
        var rowTemplate = $('#itemsBody .item-row').first().clone();
        var nextIndex = $('#itemsBody .item-row').length;

        function fmt(n) { return '{{ \App\Support\Store::currency() }} ' + n.toFixed(2); }

        function recalc() {
            var subtotal = 0;
            $('#itemsBody .item-row').each(function () {
                var opt = $(this).find('.product-select option:selected');
                var price = parseFloat(opt.data('price'));
                var qty = parseInt($(this).find('.qty-input').val(), 10) || 0;
                var stock = opt.data('stock');
                $(this).find('.qty-input').attr('max', stock !== '' && stock !== undefined ? stock : null);

                if (isNaN(price)) {
                    $(this).find('.unit-price, .line-total').text('—');
                    return;
                }
                var line = price * qty;
                subtotal += line;
                $(this).find('.unit-price').text(fmt(price));
                $(this).find('.line-total').text(fmt(line));
            });

            var discount = 0;
            var warning = '';
            var coupon = $('#couponSelect option:selected');
            if (coupon.val()) {
                var min = coupon.data('min');
                if (min !== '' && subtotal < parseFloat(min)) {
                    warning = 'This coupon needs a minimum order of {{ \App\Support\Store::currency() }} ' + parseFloat(min).toFixed(0) + '.';
                } else {
                    var value = parseFloat(coupon.data('value')) || 0;
                    discount = coupon.data('type') === 'percent' ? subtotal * value / 100 : value;
                    discount = Math.min(discount, subtotal);
                }
            }
            // Admin's own discount, applied to what's left after the coupon
            // (same order and cap as OrderController::createOrder).
            var adminValue = parseFloat($('#adminDiscountValue').val()) || 0;
            var adminIsPercent = $('#adminDiscountType').val() === 'percent';
            if (adminIsPercent && adminValue > 100) {
                warning = (warning ? warning + ' ' : '') + 'Discount can\'t be more than 100%.';
                adminValue = 100;
            }
            var remaining = subtotal - discount;
            var adminDiscount = adminIsPercent ? remaining * adminValue / 100 : adminValue;
            adminDiscount = Math.max(0, Math.min(adminDiscount, remaining));

            $('#couponWarning').toggleClass('d-none', !warning).text(warning);

            var delivery = parseFloat($('#deliveryInput').val()) || 0;
            $('#sumSubtotal').text(fmt(subtotal));
            $('#sumDiscount').text('-' + fmt(discount));
            $('#sumAdminDiscountRow').toggleClass('d-none', adminDiscount <= 0);
            $('#sumAdminDiscount').text('-' + fmt(adminDiscount));
            $('#sumDelivery').text(fmt(delivery));
            $('#sumTotal').text(fmt(Math.max(0, subtotal - discount - adminDiscount) + delivery));
        }

        $('#addItemBtn').on('click', function () {
            var row = rowTemplate.clone();
            row.find('.product-select').attr('name', 'items[' + nextIndex + '][product_id]').val('');
            row.find('.qty-input').attr('name', 'items[' + nextIndex + '][qty]').val(1);
            nextIndex++;
            $('#itemsBody').append(row);
            recalc();
        });

        $('#itemsBody').on('click', '.remove-item', function () {
            if ($('#itemsBody .item-row').length > 1) {
                $(this).closest('tr').remove();
                recalc();
            }
        });

        $('#itemsBody').on('change input', '.product-select, .qty-input', recalc);
        $('#couponSelect, #deliveryInput, #adminDiscountValue, #adminDiscountType').on('change input', recalc);

        recalc();
    });
</script>
