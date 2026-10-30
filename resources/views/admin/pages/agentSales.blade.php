@include('admin.layouts.header', ['title' => 'Agent Sales & Commissions'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h4 class="card-title mb-0">Agent Sales &amp; Commissions</h4>
                            <div class="d-flex gap-2">
                                <form method="GET" class="d-flex gap-2">
                                    <select name="agent_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">All Agents</option>
                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <a href="{{ route('admin.add-agent-sale') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Log Sale</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Agent</th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Commission %</th>
                                            <th>Commission Owed</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($sales as $sale)
                                            <tr>
                                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                                <td>{{ $sale->agent->name ?? '—' }}</td>
                                                <td>{{ $sale->product_name }}</td>
                                                <td>{{ $sale->quantity }}</td>
                                                <td>{{ money($sale->unit_price) }}</td>
                                                <td>{{ money($sale->total_amount) }}</td>
                                                <td>{{ number_format($sale->commission_rate, 2) }}%</td>
                                                <td class="text-success fw-semibold">{{ money($sale->commission_amount) }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm delete-sale" title="Delete" data-id="{{ $sale->id }}"><i class="mdi mdi-trash-can"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    No sales logged yet. <a href="{{ route('admin.add-agent-sale') }}">Log your first one</a>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if ($sales->isNotEmpty())
                                        <tfoot>
                                            <tr class="fw-semibold">
                                                <td colspan="7" class="text-end">Total commission owed (this page)</td>
                                                <td class="text-success">{{ money($sales->sum('commission_amount')) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $sales->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')

<script>
    $(document).on('click', '.delete-sale', function () {
        const id = $(this).data('id');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: 'Delete this sale record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ url('admin/agent-sales') }}/" + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                    $row.fadeOut(200, function () { $row.remove(); });
                },
            });
        });
    });
</script>
