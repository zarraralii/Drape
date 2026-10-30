@include('admin.layouts.header', ['title' => 'Contact Messages'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Contact Messages</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($contacts as $contact)
                                            <tr>
                                                <td>{{ $contact->created_at->format('M d, Y') }}</td>
                                                <td>{{ $contact->name }}</td>
                                                <td>{{ $contact->email }}</td>
                                                <td>{{ $contact->phone ?? '—' }}</td>
                                                <td>{{ $contact->subject ?? '—' }}</td>
                                                <td>{{ Str::limit($contact->message, 80) }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm delete-contact" title="Delete" data-id="{{ $contact->id }}"><i class="mdi mdi-trash-can"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">No messages yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
    $(document).on('click', '.delete-contact', function () {
        const id = $(this).data('id');
        const $row = $(this).closest('tr');
        Swal.fire({ title: 'Delete this message?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it' })
            .then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{ route('admin.contact.delete') }}", type: 'POST', data: { id },
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                        $row.fadeOut(200, function () { $row.remove(); });
                    },
                });
            });
    });
</script>
