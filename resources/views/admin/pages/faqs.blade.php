@include('admin.layouts.header', ['title' => 'FAQs'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">FAQs</h4>
                            <a href="{{ route('admin.add-faq') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add FAQ</a>
                        </div>
                        <div class="card-body">
                            <table id="allfaqstable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Question</th>
                                        <th>Answer</th>
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
        var table = $('#allfaqstable').DataTable({
            processing: true,
            serverSide: false,
            ajax: { url: "{{ route('admin.all-faqs') }}", dataSrc: 'faqs' },
            columns: [
                { data: 'id' },
                { data: 'question' },
                { data: null, render: (d) => (d.answer || '').replace(/<[^>]*>/g, '').slice(0, 80) },
                {
                    data: null,
                    render: function (data) {
                        return `<a class="btn btn-success btn-sm" title="Edit" href="{{ route('admin.add-faq') }}?id=${data.id}"><i class="mdi mdi-pencil"></i></a>
                                <button type="button" class="btn btn-danger btn-sm delete-faq" title="Delete" data-id="${data.id}"><i class="mdi mdi-trash-can"></i></button>`;
                    },
                },
            ],
            order: [[0, 'desc']],
        });

        $(document).on('click', '.delete-faq', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Delete this FAQ?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it' })
                .then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('admin.faq.delete') }}", type: 'POST', data: { id },
                        success: function (res) {
                            Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                            table.ajax.reload();
                        },
                    });
                });
        });
    });
</script>
