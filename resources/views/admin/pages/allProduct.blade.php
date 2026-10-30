@include('admin.layouts.header', ['title' => 'Products'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Products</h4>
                            <a href="{{ route('admin.add-product') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add Product</a>
                        </div>
                        <div class="card-body">
                            <table id="allproductstable" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Publish Date</th>
                                        <th>Status</th>
                                        <th>Price</th>
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
        const urlParams = new URLSearchParams(window.location.search);
        const outOfStock = urlParams.get('out_of_stock');
        const isActive = urlParams.get('is_active');

        var table = $('#allproductstable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('admin.all-product') }}",
                data: function (d) {
                    if (outOfStock) d.out_of_stock = outOfStock;
                    if (isActive) d.is_active = isActive;
                },
                dataSrc: 'products',
            },
            columns: [
                { data: 'id' },
                {
                    data: null,
                    render: function (data) {
                        const img = data.cover_image_thumbnail && data.cover_image_thumbnail !== '-' ? data.cover_image_thumbnail : '';
                        return (img ? `<img src="${img}" alt="" class="avatar-sm rounded-circle me-2">` : '') + `<span>${data.name}</span>`;
                    },
                },
                { data: 'published_date' },
                {
                    data: null,
                    render: function (data) {
                        const statusClass = data.is_active == 1 ? 'success' : 'danger';
                        const statusText = data.is_active == 1 ? 'Active' : 'Inactive';
                        return `<button type="button" class="btn btn-${statusClass} btn-rounded btn-sm active-inactive" data-product-id="${data.id}">${statusText}</button>`;
                    },
                },
                {
                    data: null,
                    render: function (data) {
                        // Prices arrive already formatted in the store currency.
                        const price = data.is_on_sale
                            ? data.current_price + ' <span class="text-muted text-decoration-line-through ms-1">' + data.price + '</span>'
                            : data.price;
                        const stock = data.variants ? data.stock + ' in stock · ' + data.variants + ' variants' : data.stock + ' in stock';
                        return price + '<br><small class="text-muted">' + stock + '</small>';
                    },
                },
                {
                    data: null,
                    render: function (data) {
                        return `<a class="btn btn-info btn-sm" title="View" href="{{ route('admin.view-product') }}?id=${data.id}"><i class="mdi mdi-eye"></i></a>
                                <a class="btn btn-success btn-sm" title="Edit" href="{{ route('admin.add-product') }}?id=${data.id}"><i class="mdi mdi-pencil"></i></a>
                                <button type="button" class="btn btn-danger btn-sm delete-product" title="Delete" data-id="${data.id}"><i class="mdi mdi-trash-can"></i></button>`;
                    },
                },
            ],
            order: [[0, 'desc']],
        });

        $(document).on('click', '.active-inactive', function () {
            const id = $(this).data('product-id');
            const isCurrentlyActive = $(this).text().trim() === 'Active';
            const next = isCurrentlyActive ? 0 : 1;

            $.post("{{ route('admin.update-approve') }}", { _token: '{{ csrf_token() }}', id, is_active: next })
                .done((res) => {
                    if (res.success) table.ajax.reload(null, false);
                });
        });

        $(document).on('click', '.delete-product', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This product will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('admin.delete-product') }}",
                    type: 'POST',
                    data: { id },
                    success: function (data) {
                        Swal.fire({ icon: 'success', title: data.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                        table.ajax.reload();
                    },
                });
            });
        });
    });
</script>
