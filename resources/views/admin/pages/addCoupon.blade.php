@include('admin.layouts.header', ['title' => isset($data['coupon']) ? 'Edit Coupon' : 'Add Coupon'])
@include('admin.layouts.menu')

@php($coupon = $data['coupon'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $coupon ? 'Edit Coupon' : 'Add Coupon' }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-coupon') }}" method="POST">
                                @csrf
                                @if ($coupon)
                                    <input type="hidden" name="id" value="{{ $coupon->id }}">
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Coupon Code</label>
                                        <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code', $coupon->code ?? '') }}" placeholder="WELCOME10" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="type" class="form-select" required>
                                            <option value="percent" {{ old('type', $coupon->type ?? '') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                            <option value="fixed" {{ old('type', $coupon->type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed ({{ \App\Support\Store::currency() }})</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Value</label>
                                        <input type="number" step="0.01" min="0" name="value" class="form-control" value="{{ old('value', $coupon->value ?? '') }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Minimum Order Amount ({{ \App\Support\Store::currency() }})</label>
                                        <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $coupon->min_order_amount ?? '') }}" placeholder="Optional">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Usage Limit</label>
                                        <input type="number" min="1" name="usage_limit" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" placeholder="Unlimited">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Expires On</label>
                                        <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', optional($coupon?->expires_at)->format('Y-m-d')) }}" placeholder="Never">
                                    </div>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>

                                <button type="submit" class="btn btn-primary">{{ $coupon ? 'Update Coupon' : 'Create Coupon' }}</button>
                                <a href="{{ route('admin.coupons') }}" class="btn btn-light">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
