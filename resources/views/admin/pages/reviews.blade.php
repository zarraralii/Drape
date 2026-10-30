@include('admin.layouts.header', ['title' => 'Reviews'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-3">Reviews</h4>
                            <form method="GET" action="{{ route('admin.reviews') }}" class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, email or review text">
                                </div>
                                <div class="col-md-3">
                                    <select name="product_id" class="form-select">
                                        <option value="">All products</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-select">
                                        <option value="">All reviews</option>
                                        <option value="visible" @selected(request('status') === 'visible')>Visible</option>
                                        <option value="hidden" @selected(request('status') === 'hidden')>Hidden</option>
                                        <option value="unreplied" @selected(request('status') === 'unreplied')>Not replied yet</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                                    <a href="{{ route('admin.reviews') }}" class="btn btn-light">Reset</a>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width:180px;">Customer</th>
                                            <th style="width:160px;">Product</th>
                                            <th>Review</th>
                                            <th style="width:110px;">Status</th>
                                            <th style="width:150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reviews as $review)
                                            <tr>
                                                <td>
                                                    <strong>{{ $review->name }}</strong><br>
                                                    <small class="text-muted">{{ $review->email }}</small><br>
                                                    <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                                    @if ($review->order)
                                                        <br><a href="{{ route('admin.view-order') }}?id={{ $review->order->id }}"><small>Order {{ $review->order->uid }}</small></a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($review->product)
                                                        <a href="{{ route('admin.view-product') }}?id={{ $review->product->id }}">{{ $review->product->name }}</a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="mb-1 text-warning">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                        @endfor
                                                    </div>
                                                    <p class="mb-2" style="white-space:pre-line;">{{ $review->review }}</p>
                                                    @if (!empty($review->media))
                                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                                            @foreach ($review->media as $attachment)
                                                                <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener">
                                                                    @if ($attachment['type'] === 'video')
                                                                        <span class="d-inline-flex align-items-center justify-content-center rounded border bg-light" style="width:60px;height:60px;"><i class="fas fa-play"></i></span>
                                                                    @else
                                                                        <img src="{{ $attachment['url'] }}" alt="" class="rounded border" style="width:60px;height:60px;object-fit:cover;">
                                                                    @endif
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <div class="review-reply border-start border-3 border-primary ps-2 {{ $review->admin_reply ? '' : 'd-none' }}">
                                                        <small class="text-primary fw-semibold">Store reply <span class="reply-date text-muted fw-normal">{{ $review->replied_at?->format('M d, Y') }}</span></small>
                                                        <p class="mb-0 reply-text" style="white-space:pre-line;">{{ $review->admin_reply }}</p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-{{ $review->is_hidden ? 'danger' : 'success' }} btn-rounded toggle-visibility"
                                                        data-id="{{ $review->id }}"
                                                        data-hidden="{{ $review->is_hidden ? 1 : 0 }}"
                                                        title="Click to {{ $review->is_hidden ? 'show' : 'hide' }}"
                                                    >
                                                        {{ $review->is_hidden ? 'Hidden' : 'Visible' }}
                                                    </button>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm reply-review" title="Reply" data-id="{{ $review->id }}" data-reply="{{ $review->admin_reply }}"><i class="mdi mdi-reply"></i></button>
                                                    <button type="button" class="btn btn-danger btn-sm delete-review" title="Delete" data-id="{{ $review->id }}"><i class="mdi mdi-trash-can"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No reviews found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $reviews->links() }}
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
    function reviewToast(icon, title) {
        Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
    }

    $(document).on('click', '.toggle-visibility', function () {
        const $btn = $(this);
        const id = $btn.data('id');
        const nextHidden = $btn.data('hidden') ? 0 : 1;

        $.post("{{ route('admin.reviews.visibility') }}", { _token: '{{ csrf_token() }}', id, is_hidden: nextHidden })
            .done(function (res) {
                if (!res.success) return;
                $btn.data('hidden', nextHidden)
                    .text(nextHidden ? 'Hidden' : 'Visible')
                    .attr('title', 'Click to ' + (nextHidden ? 'show' : 'hide'))
                    .removeClass('btn-success btn-danger')
                    .addClass(nextHidden ? 'btn-danger' : 'btn-success');
                reviewToast('success', res.message);
            })
            .fail(() => reviewToast('error', 'Could not update the review'));
    });

    $(document).on('click', '.reply-review', function () {
        const $btn = $(this);
        const $row = $btn.closest('tr');

        Swal.fire({
            title: 'Reply to review',
            input: 'textarea',
            inputValue: $btn.data('reply') || '',
            inputPlaceholder: 'Your reply is shown publicly under the review. Leave empty to remove it.',
            inputAttributes: { maxlength: 2000, rows: 5 },
            showCancelButton: true,
            confirmButtonText: 'Save reply',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post("{{ route('admin.reviews.reply') }}", { _token: '{{ csrf_token() }}', id: $btn.data('id'), admin_reply: result.value })
                .done(function (res) {
                    $btn.data('reply', res.reply || '');
                    const $reply = $row.find('.review-reply');
                    $reply.find('.reply-text').text(res.reply || '');
                    $reply.find('.reply-date').text(res.replied_at || '');
                    $reply.toggleClass('d-none', !res.reply);
                    reviewToast('success', res.message);
                })
                .fail(() => reviewToast('error', 'Could not save the reply'));
        });
    });

    $(document).on('click', '.delete-review', function () {
        const id = $(this).data('id');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: 'Delete this review?',
            text: 'It will be removed permanently and the customer will be able to review this product again.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ url('admin/reviews') }}/" + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    reviewToast('success', res.message);
                    $row.fadeOut(200, function () { $row.remove(); });
                },
                error: () => reviewToast('error', 'Could not delete the review'),
            });
        });
    });
</script>
