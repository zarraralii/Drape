@include('admin.layouts.header', ['title' => 'Banners'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Homepage Banners</h4>
                            <a href="{{ route('admin.add-banner') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add Banner</a>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">These appear as slides in the homepage hero carousel, in the order set below. At least one active banner is needed for the carousel to show — otherwise the homepage falls back to a single static hero.</p>
                            <table id="allbannerstable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Banner</th>
                                        <th>Order</th>
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
        var table = $('#allbannerstable').DataTable({
            processing: true,
            serverSide: false,
            ajax: { url: "{{ route('admin.all-banners') }}", dataSrc: 'banners' },
            columns: [
                { data: 'id' },
                {
                    data: null,
                    render: function (data) {
                        return (data.image ? `<img src="${data.image}" class="avatar-sm rounded me-2">` : '') + `<span>${data.headline}</span>`;
                    },
                },
                { data: 'sort_order' },
                {
                    data: null,
                    render: function (data) {
                        const cls = data.is_active == 1 ? 'success' : 'danger';
                        const text = data.is_active == 1 ? 'Active' : 'Inactive';
                        return `<button type="button" class="btn btn-${cls} btn-rounded btn-sm toggle-status" data-id="${data.id}">${text}</button>`;
                    },
                },
                {
                    data: null,
                    render: function (data) {
                        return `<a class="btn btn-success btn-sm" title="Edit" href="{{ route('admin.add-banner') }}?id=${data.id}"><i class="mdi mdi-pencil"></i></a>
                                <button type="button" class="btn btn-danger btn-sm delete-banner" title="Delete" data-id="${data.id}"><i class="mdi mdi-trash-can"></i></button>`;
                    },
                },
            ],
            order: [[2, 'asc']],
        });

        $(document).on('click', '.toggle-status', function () {
            const id = $(this).data('id');
            const next = $(this).text().trim() === 'Active' ? 0 : 1;
            $.post("{{ route('admin.banners.status') }}", { _token: '{{ csrf_token() }}', id, is_active: next })
                .done((res) => { if (res.success) table.ajax.reload(null, false); });
        });

        $(document).on('click', '.delete-banner', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Delete this banner?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it' })
                .then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('admin.banners.delete') }}", type: 'POST', data: { id },
                        success: function (res) {
                            Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                            table.ajax.reload();
                        },
                    });
                });
        });
    });
</script>
