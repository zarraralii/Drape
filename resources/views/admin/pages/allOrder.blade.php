@include('admin.layouts.header', ['title' => 'Orders'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Orders</h4>
                            <a href="{{ route('admin.create-order') }}" class="btn btn-primary btn-sm"><i class="mdi mdi-plus"></i> Create Order</a>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 align-items-end mb-3" id="orderFilters">
                                <div class="col-md-3">
                                    <label class="form-label">Agent</label>
                                    <select id="filterAgent" class="form-select">
                                        <option value="">All Agents</option>
                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Coupon Code</label>
                                    <select id="filterCoupon" class="form-select">
                                        <option value="">All Coupon Codes</option>
                                        @foreach ($coupons as $code)
                                            <option value="{{ $code }}">{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select id="filterStatus" class="form-select">
                                        <option value="">All Status</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="filterApplyBtn" class="btn btn-primary">Filter</button>
                                    <button type="button" id="filterResetBtn" class="btn btn-outline-secondary">Reset</button>
                                </div>
                            </div>

                            <table id="allorderstable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>UID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')

<script>
    $(document).ready(function () {
        const statusColors = { Pending: 'warning', Processing: 'info', Shipped: 'primary', Delivered: 'success', Cancelled: 'danger' };
        const baseAjaxUrl = "{{ route('admin.all-order') }}";
        const basePageUrl = "{{ route('admin.orders') }}";

        // Filters are read straight from the page's own URL on load (and
        // written back to it on every Filter/Reset) so the filtered view
        // survives a refresh or gets shared as a link — the orders table
        // itself still loads its (now filtered) data in one request and
        // paginates/searches/sorts that result entirely client-side, same
        // as before.
        function currentFilters() {
            var params = new URLSearchParams(window.location.search);
            return {
                agent_id: params.get('agent_id') || '',
                coupon_code: params.get('coupon_code') || '',
                status: params.get('status') || '',
            };
        }

        function applyFiltersToUi(filters) {
            $('#filterAgent').val(filters.agent_id || '');
            $('#filterCoupon').val(filters.coupon_code || '');
            $('#filterStatus').val(filters.status || '');
        }

        function queryStringFor(filters) {
            var query = new URLSearchParams();
            if (filters.agent_id) query.set('agent_id', filters.agent_id);
            if (filters.coupon_code) query.set('coupon_code', filters.coupon_code);
            if (filters.status) query.set('status', filters.status);
            return query.toString();
        }

        function ajaxUrlFor(filters) {
            var qs = queryStringFor(filters);
            return baseAjaxUrl + (qs ? '?' + qs : '');
        }

        var initialFilters = currentFilters();
        applyFiltersToUi(initialFilters);

        var table = $('#allorderstable').DataTable({
            processing: true,
            serverSide: false,
            ajax: { url: ajaxUrlFor(initialFilters), dataSrc: 'orders' },
            columns: [
                { data: 'id' },
                { data: 'uid', render: (d) => d || '—' },
                {
                    data: null,
                    render: function (data) {
                        return (data.user_profile_image ? `<img src="${data.user_profile_image}" class="avatar-sm rounded-circle me-2">` : '') + (data.user_name || 'Guest');
                    },
                },
                { data: 'item_counts' },
                { data: 'total_amount', render: (d) => d === '-' ? d : '{{ \App\Support\Store::currency() }} ' + Math.round(parseFloat(d)).toLocaleString('en-US') },
                {
                    data: 'status',
                    render: function (status) {
                        const cls = statusColors[status] || 'secondary';
                        return `<span class="badge bg-${cls}">${status || '—'}</span>`;
                    },
                },
                {
                    data: null,
                    render: function (data) {
                        return `<a class="btn btn-info btn-sm has-label" href="{{ route('admin.view-order') }}?id=${data.id}"><i class="mdi mdi-eye"></i> View</a>`;
                    },
                },
            ],
            order: [[0, 'desc']],
        });

        function reloadWith(filters) {
            var qs = queryStringFor(filters);

            // Keeps /admin/orders itself shareable/refreshable with the
            // current filters, without a full page reload.
            var newPageUrl = basePageUrl + (qs ? '?' + qs : '');
            window.history.replaceState(null, '', newPageUrl);

            table.ajax.url(ajaxUrlFor(filters)).load();
        }

        $('#filterApplyBtn').on('click', function () {
            reloadWith({
                agent_id: $('#filterAgent').val(),
                coupon_code: $('#filterCoupon').val(),
                status: $('#filterStatus').val(),
            });
        });

        $('#filterResetBtn').on('click', function () {
            applyFiltersToUi({ agent_id: '', coupon_code: '', status: '' });
            reloadWith({ agent_id: '', coupon_code: '', status: '' });
        });
    });
</script>
