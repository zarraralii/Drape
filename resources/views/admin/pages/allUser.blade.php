@include('admin.layouts.header', ['title' => 'Users'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Users</h4>
                        </div>
                        <div class="card-body">
                            <table id="allusersable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Orders</th>
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
        $('#allusersable').DataTable({
            processing: true,
            serverSide: false,
            ajax: { url: "{{ route('admin.all-user') }}", dataSrc: 'users' },
            columns: [
                { data: 'id' },
                {
                    data: null,
                    render: function (data) {
                        return (data.profile_image ? `<img src="${data.profile_image}" class="avatar-sm rounded-circle me-2">` : '') + (data.name || '—');
                    },
                },
                { data: 'email' },
                { data: 'total_orders' },
                {
                    data: null,
                    render: function (data) {
                        return `<a class="btn btn-info btn-sm has-label" href="{{ route('admin.view-user') }}?id=${data.id}"><i class="mdi mdi-eye"></i> View</a>`;
                    },
                },
            ],
            order: [[0, 'desc']],
        });
    });
</script>
