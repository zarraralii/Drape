@include('admin.layouts.header', ['title' => 'Log Agent Sale'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Log a Sale / Commission</h4>
                            <p class="text-muted mb-0 small">Record stock an agent bought from us, or a sale they referred — the commission owed is calculated automatically.</p>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-agent-sale') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Agent</label>
                                        <select name="agent_id" id="agent_id" class="form-select" required>
                                            <option value="">Select an agent…</option>
                                            @foreach ($data['agents'] as $agent)
                                                <option value="{{ $agent->id }}" data-commission="{{ $agent->commission_rate }}">
                                                    {{ $agent->name }} ({{ ucfirst($agent->type) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($data['agents']->isEmpty())
                                            <small class="text-danger">No agents yet — <a href="{{ route('admin.add-agent') }}">add one first</a>.</small>
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Sale Date</label>
                                        <input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', now()->toDateString()) }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Product</label>
                                        <select name="product_id" id="product_id" class="form-select">
                                            <option value="">Custom / not in catalogue…</option>
                                            @foreach ($data['products'] as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} ({{ money($product->price) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="customProductWrap">
                                        <label class="form-label">Product Name</label>
                                        <input type="text" name="product_name" id="product_name" class="form-control" placeholder="e.g. Relaxed Linen Shirt — White / M">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" min="1" name="quantity" id="quantity" class="form-control" value="{{ old('quantity', 1) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Unit Price ({{ \App\Support\Store::currency() }})</label>
                                        <input type="number" step="0.01" min="0" name="unit_price" id="unit_price" class="form-control" value="{{ old('unit_price') }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Commission Rate (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" name="commission_rate" id="commission_rate" class="form-control" value="{{ old('commission_rate', 10) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Commission Owed</label>
                                        <input type="text" id="commission_preview" class="form-control" value="{{ \App\Support\Store::currency() }} 0" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" rows="2" class="form-control" placeholder="Optional — invoice #, referral details, etc.">{{ old('notes') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">Save Sale</button>
                                <a href="{{ route('admin.agent-sales') }}" class="btn btn-light">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')

<script>
    const agentSelect = document.getElementById('agent_id');
    const productSelect = document.getElementById('product_id');
    const customWrap = document.getElementById('customProductWrap');
    const unitPrice = document.getElementById('unit_price');
    const quantity = document.getElementById('quantity');
    const commissionRate = document.getElementById('commission_rate');
    const commissionPreview = document.getElementById('commission_preview');

    agentSelect.addEventListener('change', function () {
        const rate = this.selectedOptions[0]?.dataset.commission;
        if (rate) commissionRate.value = rate;
        updatePreview();
    });

    productSelect.addEventListener('change', function () {
        const price = this.selectedOptions[0]?.dataset.price;
        customWrap.style.display = this.value ? 'none' : 'block';
        if (price) unitPrice.value = price;
        updatePreview();
    });

    function updatePreview() {
        const total = (parseFloat(unitPrice.value) || 0) * (parseInt(quantity.value) || 0);
        const commission = total * ((parseFloat(commissionRate.value) || 0) / 100);
        commissionPreview.value = '{{ \App\Support\Store::currency() }} ' + commission.toFixed(2);
    }

    [unitPrice, quantity, commissionRate].forEach((el) => el.addEventListener('input', updatePreview));
    updatePreview();
</script>
