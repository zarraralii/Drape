@include('admin.layouts.header', ['title' => 'Collections'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Collections</h4>
                            <a href="{{ route('admin.add-category') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add Collection</a>
                        </div>
                        <div class="card-body">
                            <table id="allcategoriestable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
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
        var table = $('#allcategoriestable').DataTable({
            processing: true,
            serverSide: false,
            ajax: { url: "{{ route('admin.all-categories') }}", dataSrc: 'categories' },
            columns: [
                { data: 'id' },
                {
                    data: null,
                    render: function (data) {
                        return (data.cover_image ? `<img src="${data.cover_image}" class="avatar-sm rounded-circle me-2">` : '') + `<span>${data.name}</span>`;
                    },
                },
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
                        return `<a class="btn btn-success btn-sm" title="Edit" href="{{ route('admin.add-category') }}?id=${data.id}"><i class="mdi mdi-pencil"></i></a>
                                <button type="button" class="btn btn-danger btn-sm delete-category" title="Delete" data-id="${data.id}"><i class="mdi mdi-trash-can"></i></button>`;
                    },
                },
            ],
            order: [[0, 'desc']],
        });

        $(document).on('click', '.toggle-status', function () {
            const id = $(this).data('id');
            const next = $(this).text().trim() === 'Active' ? 0 : 1;
            $.post("{{ route('admin.category.status') }}", { _token: '{{ csrf_token() }}', id, is_active: next })
                .done((res) => { if (res.success) table.ajax.reload(null, false); });
        });

        $(document).on('click', '.delete-category', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Delete this category?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it' })
                .then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('admin.category.delete') }}", type: 'POST', data: { id },
                        success: function (res) {
                            Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                            table.ajax.reload();
                        },
                    });
                });
        });
    });
</script>
