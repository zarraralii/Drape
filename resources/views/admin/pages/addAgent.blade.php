@include('admin.layouts.header', ['title' => isset($data['agent']) ? 'Edit Agent' : 'Add Agent'])
@include('admin.layouts.menu')

@php($agent = $data['agent'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $agent ? 'Edit Agent' : 'Add Agent' }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-agent') }}" method="POST">
                                @csrf
                                @if ($agent)
                                    <input type="hidden" name="id" value="{{ $agent->id }}">
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $agent->name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="type" class="form-select" required>
                                            <option value="vendor" {{ old('type', $agent->type ?? '') === 'vendor' ? 'selected' : '' }}>Vendor — buys stock from us to resell</option>
                                            <option value="referrer" {{ old('type', $agent->type ?? '') === 'referrer' ? 'selected' : '' }}>Referrer — sends customers to buy here</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $agent->email ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $agent->phone ?? '') }}">
                                    </div>
                                </div>

                                @if ($agent?->referral_code)
                                    <div class="mb-3">
                                        <label class="form-label">Referral Code</label>
                                        <input type="text" class="form-control" value="{{ $agent->referral_code }}" readonly>
                                        <small class="text-muted">Give this to the agent — customers enter it at checkout so their order is attributed here for commission.</small>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Default Commission Rate (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-control" value="{{ old('commission_rate', $agent->commission_rate ?? 10) }}" required>
                                        <small class="text-muted">Used to pre-fill new sale entries for this agent — can be overridden per sale.</small>
                                    </div>
                                    <div class="col-md-8 mb-3 d-flex align-items-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $agent->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $agent->notes ?? '') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">{{ $agent ? 'Update Agent' : 'Add Agent' }}</button>
                                <a href="{{ route('admin.agents') }}" class="btn btn-light">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
