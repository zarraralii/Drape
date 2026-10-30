@include('admin.layouts.header', ['title' => 'Coupons'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Coupons</h4>
                            <a href="{{ route('admin.add-coupon') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add Coupon</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Type</th>
                                            <th>Value</th>
                                            <th>Min Order</th>
                                            <th>Usage</th>
                                            <th>Expires</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($coupons as $coupon)
                                            <tr>
                                                <td><code>{{ $coupon->code }}</code></td>
                                                <td>{{ $coupon->type === 'percent' ? 'Percent' : 'Fixed' }}</td>
                                                <td>{{ $coupon->type === 'percent' ? $coupon->value . '%' : money($coupon->value) }}</td>
                                                <td>{{ $coupon->min_order_amount ? money($coupon->min_order_amount) : '—' }}</td>
                                                <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}</td>
                                                <td>{{ $coupon->expires_at?->format('M d, Y') ?? 'Never' }}</td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-{{ $coupon->is_active ? 'success' : 'danger' }} btn-rounded toggle-status"
                                                        data-id="{{ $coupon->id }}"
                                                        data-active="{{ $coupon->is_active ? 1 : 0 }}"
                                                    >
                                                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                                    </button>
                                                    @if ($coupon->isExpired())
                                                        <span class="badge bg-secondary ms-1">Expired</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-success btn-sm" title="Edit" href="{{ route('admin.add-coupon') }}?id={{ $coupon->id }}"><i class="mdi mdi-pencil"></i></a>
                                                    <button type="button" class="btn btn-danger btn-sm delete-coupon" title="Delete" data-id="{{ $coupon->id }}"><i class="mdi mdi-trash-can"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    No coupons yet. <a href="{{ route('admin.add-coupon') }}">Create your first one</a>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $coupons->links() }}
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
    $(document).on('click', '.toggle-status', function () {
        const $btn = $(this);
        const id = $btn.data('id');
        const nextActive = $btn.data('active') ? 0 : 1;

        $.post("{{ route('admin.coupons.status') }}", { _token: '{{ csrf_token() }}', id, is_active: nextActive })
            .done(function (res) {
                if (res.success) {
                    $btn.data('active', nextActive)
                        .text(nextActive ? 'Active' : 'Inactive')
                        .removeClass('btn-success btn-danger')
                        .addClass(nextActive ? 'btn-success' : 'btn-danger');
                }
            });
    });

    $(document).on('click', '.delete-coupon', function () {
        const id = $(this).data('id');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: 'Delete this coupon?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ url('admin/coupons') }}/" + id,
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
