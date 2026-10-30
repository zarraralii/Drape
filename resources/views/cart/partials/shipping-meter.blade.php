@if ($free_shipping_threshold)
    @php($progress = min(100, round((($subtotal - $discount) / $free_shipping_threshold) * 100)))
    <div class="ship-meter">
        <p>
            @if ($free_shipping_remaining > 0)
                You're <strong>{{ money($free_shipping_remaining) }}</strong> away from free delivery.
            @else
                <x-icon name="check" /> You've unlocked <strong>free delivery</strong>.
            @endif
        </p>
        <div class="ship-meter__bar"><span style="width: {{ $progress }}%"></span></div>
    </div>
@endif
