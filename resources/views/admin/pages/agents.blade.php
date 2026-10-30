@include('admin.layouts.header', ['title' => 'Agents'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="card-title mb-0">Agents</h4>
                                <p class="text-muted mb-0 small">Vendors who buy stock from us to resell, or people who refer customers to the site — both earn commission.</p>
                            </div>
                            <a href="{{ route('admin.add-agent') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add Agent</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Type</th>
                                            <th>Referral Code</th>
                                            <th>Default Commission</th>
                                            <th>Orders</th>
                                            <th>Total Sold</th>
                                            <th>Commission Earned</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($agents as $agent)
                                            <tr>
                                                <td>{{ $agent->name }}</td>
                                                <td>
                                                    {{ $agent->email ?? '—' }}
                                                    @if ($agent->phone)
                                                        <div class="text-muted small">{{ $agent->phone }}</div>
                                                    @endif
                                                </td>
                                                <td><span class="badge bg-{{ $agent->type === 'vendor' ? 'primary' : 'info' }}">{{ ucfirst($agent->type) }}</span></td>
                                                <td><code>{{ $agent->referral_code ?? '—' }}</code></td>
                                                <td>{{ number_format($agent->commission_rate, 2) }}%</td>
                                                <td>{{ $agent->orders_count ?? 0 }}</td>
                                                <td>{{ $agent->sales_sum_quantity ?? 0 }} units</td>
                                                <td class="text-success fw-semibold">{{ money($agent->sales_sum_commission_amount ?? 0) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $agent->is_active ? 'success' : 'danger' }}">{{ $agent->is_active ? 'Active' : 'Inactive' }}</span>
                                                </td>
                                                <td>
                                                    <a class="btn btn-success btn-sm" title="Edit" href="{{ route('admin.add-agent') }}?id={{ $agent->id }}"><i class="mdi mdi-pencil"></i></a>
                                                    <a class="btn btn-info btn-sm" title="View sales" href="{{ route('admin.agent-sales', ['agent_id' => $agent->id]) }}"><i class="mdi mdi-eye"></i></a>
                                                    <button type="button" class="btn btn-danger btn-sm delete-agent" title="Delete" data-id="{{ $agent->id }}"><i class="mdi mdi-trash-can"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">
                                                    No agents yet. <a href="{{ route('admin.add-agent') }}">Add your first one</a>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $agents->links() }}
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
    $(document).on('click', '.delete-agent', function () {
        const id = $(this).data('id');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: 'Delete this agent?',
            text: 'Their logged sales will be deleted too.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ url('admin/agents') }}/" + id,
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
