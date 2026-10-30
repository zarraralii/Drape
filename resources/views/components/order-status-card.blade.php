@props(['order', 'allowReviews' => false])

@php
    $isCancelled = $order->status === 'Cancelled';
    $stages = ['Pending' => ['Order placed', 'clock'], 'Processing' => ['Preparing', 'package'], 'Shipped' => ['On its way', 'truck'], 'Delivered' => ['Delivered', 'check']];
    $stageKeys = array_keys($stages);
    $currentIndex = array_search($order->status, $stageKeys, true);
    $reviewedProductIds = $allowReviews ? \App\Models\Review::where('order_id', $order->id)->pluck('product_id')->all() : [];
@endphp

<div class="order-card">
    <header class="order-card__head">
        <div>
            <span class="muted small">Order</span>
            <strong>{{ $order->uid }}</strong>
        </div>
        <div>
            <span class="muted small">Placed</span>
            <strong>{{ $order->created_at->format('M d, Y') }}</strong>
        </div>
        <div>
            <span class="muted small">Total</span>
            <strong>{{ money($order->total_amount) }}</strong>
        </div>
        <span class="status status--{{ Str::slug($order->status) }}">{{ $order->status }}</span>
    </header>

    @if ($isCancelled)
        <p class="order-card__notice">This order was cancelled.@if ($order->rejection_reason) {{ $order->rejection_reason }}@endif</p>
    @else
        <ol class="progress">
            @foreach ($stages as $key => [$label, $icon])
                <li @class(['is-done' => $currentIndex !== false && $loop->index <= $currentIndex, 'is-current' => $loop->index === $currentIndex])>
                    <span class="progress__dot"><x-icon :name="$icon" /></span>
                    <span class="progress__label">{{ $label }}</span>
                </li>
            @endforeach
        </ol>
        @if ($order->tracking)
            <p class="order-card__notice">
                Shipped with {{ $order->tracking->courier }} — tracking no. <strong>{{ $order->tracking->tracking_number }}</strong>
                @if ($order->tracking->courier_link)
                    · <a href="{{ safe_link($order->tracking->courier_link, '#') }}" target="_blank" rel="noopener">Track with courier</a>
                @endif
            </p>
        @endif
    @endif

    <div class="order-card__body">
        <ul class="order-lines">
            @foreach ($order->items as $item)
                <li>
                    <span>
                        {{ $item->display_name }}
                        @if ($item->variant_label)<small class="muted">{{ $item->variant_label }}</small>@endif
                        × {{ $item->qty }}
                    </span>
                    <span>{{ money($item->item_total) }}</span>

                    @if ($allowReviews && $order->status === 'Delivered' && $item->item && $item->item->is_active)
                        <div class="order-lines__review">
                            @if (in_array($item->item_id, $reviewedProductIds))
                                <span class="muted small"><x-icon name="check" /> Reviewed — thank you</span>
                            @else
                                <details>
                                    <summary class="link-btn"><x-icon name="star" /> Write a review</summary>
                                    <form method="POST" action="{{ route('reviews.store', $item->item->slug) }}" enctype="multipart/form-data" class="review-form">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                        <div class="form-grid">
                                            <div class="field"><label>Name</label><input type="text" name="name" required value="{{ $order->contact_name }}"></div>
                                            <div class="field"><label>Email</label><input type="email" name="email" required value="{{ $order->contact_email }}"></div>
                                        </div>
                                        <div class="field"><label>Rating</label><x-star-rating-input :id="'rating-' . $order->id . '-' . $item->id" /></div>
                                        <div class="field"><label>Your review</label><textarea name="review" rows="3" required></textarea></div>
                                        <div class="field"><label>Photos or videos <span class="muted">(optional)</span></label><x-review-media-input :id="'media-' . $order->id . '-' . $item->id" /></div>
                                        <button type="submit" class="btn btn--primary btn--sm">Submit review</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="order-card__meta">
            <div>
                <h3>Delivery</h3>
                <p>{{ $order->delivery?->full_address ?? '—' }}<br>{{ collect([$order->delivery?->city, $order->delivery?->state])->filter()->implode(', ') }}</p>
            </div>
            <div>
                <h3>Payment</h3>
                <p>{{ $order->payment_method }}</p>
            </div>
        </div>

        @if ($order->trails->isNotEmpty())
            <details class="order-card__history">
                <summary>Order history</summary>
                <ul>
                    @foreach ($order->trails as $trail)
                        <li><span>{{ $trail->status }}</span><time>{{ $trail->created_at->format('M d, Y · g:ia') }}</time></li>
                    @endforeach
                </ul>
            </details>
        @endif
    </div>
</div>
