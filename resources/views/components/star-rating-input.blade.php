@props(['id' => 'rating'])

<div class="star-rating-input" data-star-rating>
    <input type="hidden" name="rating" id="{{ $id }}" value="{{ old('rating') }}" required data-star-rating-value>
    <div class="star-rating-input-stars">
        @for ($i = 1; $i <= 5; $i++)
            <button type="button" class="star-rating-input-star" data-star-rating-star="{{ $i }}" aria-label="{{ $i }} {{ Str::plural('star', $i) }}">
                <x-icon name="star" />
            </button>
        @endfor
    </div>
</div>

@once
    @push('scripts')
    <script>
        (function () {
            document.addEventListener('click', function (e) {
                var star = e.target.closest('[data-star-rating-star]');
                if (!star) return;

                var wrap = star.closest('[data-star-rating]');
                var value = parseInt(star.getAttribute('data-star-rating-star'), 10);
                wrap.querySelector('[data-star-rating-value]').value = value;

                wrap.querySelectorAll('[data-star-rating-star]').forEach(function (btn) {
                    var btnValue = parseInt(btn.getAttribute('data-star-rating-star'), 10);
                    var filled = btnValue <= value;
                    btn.classList.toggle('is-filled', filled);
                });
            });

            document.addEventListener('mouseover', function (e) {
                var star = e.target.closest('[data-star-rating-star]');
                if (!star) return;

                var wrap = star.closest('[data-star-rating]');
                var value = parseInt(star.getAttribute('data-star-rating-star'), 10);

                wrap.querySelectorAll('[data-star-rating-star]').forEach(function (btn) {
                    var btnValue = parseInt(btn.getAttribute('data-star-rating-star'), 10);
                    btn.classList.toggle('is-hovered', btnValue <= value);
                });
            });

            document.addEventListener('mouseout', function (e) {
                var wrap = e.target.closest('[data-star-rating]');
                if (!wrap) return;

                wrap.querySelectorAll('[data-star-rating-star]').forEach(function (btn) {
                    btn.classList.remove('is-hovered');
                });
            });
        })();
    </script>
    @endpush
@endonce
