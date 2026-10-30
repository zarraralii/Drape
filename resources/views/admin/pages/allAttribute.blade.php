@include('admin.layouts.header', ['title' => 'Options'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Options <small class="text-muted fw-normal">— sizes, colours and other choices used to build product variants</small></h4>
                            <a href="{{ route('admin.add-attribute') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add Option</a>
                        </div>
                        <div class="card-body">
                            <table id="allattributestable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Values</th>
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
        var table = $('#allattributestable').DataTable({
            processing: true,
            serverSide: false,
            ajax: { url: "{{ route('admin.all-attributes') }}", dataSrc: 'attributes' },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: null, render: (d) => (d.values || []).map(v => `<span class="badge bg-light text-dark border me-1 mb-1 option-badge">${v.swatch ? `<i style="background:${v.swatch}"></i>` : ''}${$('<div>').text(v.value).html()}</span>`).join('') },
                {
                    data: null,
                    render: function (data) {
                        return `<a class="btn btn-success btn-sm" title="Edit" href="{{ route('admin.add-attribute') }}?id=${data.id}"><i class="mdi mdi-pencil"></i></a>
                                <button type="button" class="btn btn-danger btn-sm delete-attribute" title="Delete" data-id="${data.id}"><i class="mdi mdi-trash-can"></i></button>`;
                    },
                },
            ],
            order: [[0, 'desc']],
        });

        $(document).on('click', '.delete-attribute', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Delete this option and all its values?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it' })
                .then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('admin.attribute.delete') }}", type: 'POST', data: { id },
                        success: function (res) {
                            Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                            table.ajax.reload();
                        },
                    });
                });
        });
    });
</script>
