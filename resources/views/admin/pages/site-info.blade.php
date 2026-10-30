@include('admin.layouts.header', ['title' => 'Site Info'])
@include('admin.layouts.menu')

@php($siteInfo = $data['site_info'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Site Info</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.siteinfo') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <ul class="nav nav-tabs mb-3" role="tablist">
                                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-brand">Brand</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-homepage">Homepage</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-contact">Contact</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-policies">Policies</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-banners">Banners</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-seo">SEO</a></li>
                                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-gallery">Gallery</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="tab-brand">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-md-7 mb-3">
                                                        <label class="form-label">Brand Name</label>
                                                        <input type="text" name="brand_name" class="form-control" value="{{ old('brand_name', $siteInfo->brand_name ?? 'Drape') }}" required>
                                                    </div>
                                                    <div class="col-md-5 mb-3">
                                                        <label class="form-label">Tagline</label>
                                                        <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $siteInfo->tagline ?? '') }}" placeholder="Clothing made to move with you">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">About</label>
                                                    <textarea id="about" name="about" rows="4" class="form-control">{{ old('about', $siteInfo->about ?? '') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <x-admin-image-field field="logo" label="Logo" :value="$siteInfo?->logo" preview-id="previewLogo" input-id="logoInput" />
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <h5>Store settings</h5>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label">Announcement bar</label>
                                                <input type="text" name="announcement_text" class="form-control" maxlength="255" value="{{ old('announcement_text', $siteInfo->announcement_text ?? '') }}" placeholder="Free delivery on orders over PKR 5,000 · Cash on delivery nationwide">
                                                <small class="text-muted">The thin strip above the header on every page. Leave blank to hide it.</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Currency label</label>
                                                <input type="text" name="currency" class="form-control" maxlength="10" value="{{ old('currency', $siteInfo->currency ?? 'PKR') }}" placeholder="PKR">
                                                <small class="text-muted">Shown before every price, e.g. PKR or Rs.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="tab-homepage">
                                        <h5>Hero Section</h5>
                                        <p class="text-muted small">The full-screen intro at the very top of the homepage.</p>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Eyebrow</label>
                                                <input type="text" name="hero_eyebrow" class="form-control" value="{{ old('hero_eyebrow', $siteInfo->hero_eyebrow ?? 'New Season') }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Subheadline</label>
                                                <input type="text" name="hero_subheadline" class="form-control" value="{{ old('hero_subheadline', $siteInfo->hero_subheadline ?? 'Made to move with you') }}">
                                                <small class="text-muted">Shown in italics under the brand name.</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Button Label</label>
                                                <input type="text" name="hero_cta_label" class="form-control" value="{{ old('hero_cta_label', $siteInfo->hero_cta_label ?? 'Shop New Arrivals') }}">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="hero_description" rows="3" class="form-control">{{ old('hero_description', $siteInfo->hero_description ?? '') }}</textarea>
                                            <small class="text-muted">Shown under the headline on the homepage hero.</small>
                                        </div>

                                        <hr class="my-4">

                                        <h5>Promo Banner Strip</h5>
                                        <p class="text-muted small">Shown right before "Shop by Category" — Left and Right sit side by side, with the Full banner spanning the row below them. Each is optional and can link anywhere (leave the link blank to send it to the Shop page).</p>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <x-admin-image-field field="promo_banner_left" label="Left Banner" :value="$siteInfo?->promo_banner_left" preview-id="previewPromoLeft" input-id="promoLeftInput" />
                                                <input type="text" name="promo_banner_left_label" class="form-control mb-2" value="{{ old('promo_banner_left_label', $siteInfo->promo_banner_left_label ?? '') }}" placeholder="Button Text (default: Shop)">
                                                <input type="text" name="promo_banner_left_link" class="form-control" value="{{ old('promo_banner_left_link', $siteInfo->promo_banner_left_link ?? '') }}" placeholder="/collections/all (default: Shop)">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <x-admin-image-field field="promo_banner_right" label="Right Banner" :value="$siteInfo?->promo_banner_right" preview-id="previewPromoRight" input-id="promoRightInput" />
                                                <input type="text" name="promo_banner_right_label" class="form-control mb-2" value="{{ old('promo_banner_right_label', $siteInfo->promo_banner_right_label ?? '') }}" placeholder="Button Text (default: Shop)">
                                                <input type="text" name="promo_banner_right_link" class="form-control" value="{{ old('promo_banner_right_link', $siteInfo->promo_banner_right_link ?? '') }}" placeholder="/collections/all (default: Shop)">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <x-admin-image-field field="promo_banner_full" label="Full Banner" :value="$siteInfo?->promo_banner_full" preview-id="previewPromoFull" input-id="promoFullInput" max-height="160px" />
                                                <input type="text" name="promo_banner_full_label" class="form-control mb-2" value="{{ old('promo_banner_full_label', $siteInfo->promo_banner_full_label ?? '') }}" placeholder="Button Text (default: Shop)">
                                                <input type="text" name="promo_banner_full_link" class="form-control" value="{{ old('promo_banner_full_link', $siteInfo->promo_banner_full_link ?? '') }}" placeholder="/collections/all (default: Shop)">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="tab-contact">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email', $siteInfo->email ?? '') }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $siteInfo->phone ?? '') }}">
                                            </div>
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" name="location" class="form-control" value="{{ old('location', $siteInfo->location ?? '') }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Service Timing</label>
                                                <input type="text" name="service_timing" class="form-control" value="{{ old('service_timing', $siteInfo->service_timing ?? '') }}" placeholder="Mon–Sat, 9am–6pm">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Instagram URL</label>
                                                <input type="text" name="instagram_link" class="form-control" value="{{ old('instagram_link', $siteInfo->instagram_link ?? '') }}">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Facebook URL</label>
                                                <input type="text" name="facebook_link" class="form-control" value="{{ old('facebook_link', $siteInfo->facebook_link ?? '') }}">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">TikTok URL</label>
                                                <input type="text" name="tiktok_link" class="form-control" value="{{ old('tiktok_link', $siteInfo->tiktok_link ?? '') }}">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">YouTube URL</label>
                                                <input type="text" name="youtube_link" class="form-control" value="{{ old('youtube_link', $siteInfo->youtube_link ?? '') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="tab-policies">
                                        <div class="mb-3">
                                            <label class="form-label">Privacy Policy</label>
                                            <textarea name="privacy_policy" rows="4" class="form-control">{{ old('privacy_policy', $siteInfo->privacy_policy ?? '') }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Terms &amp; Conditions</label>
                                            <textarea name="tnc" rows="4" class="form-control">{{ old('tnc', $siteInfo->tnc ?? '') }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Return &amp; Exchange Policy</label>
                                            <textarea name="return_and_exchange" rows="4" class="form-control">{{ old('return_and_exchange', $siteInfo->return_and_exchange ?? '') }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Shipping Policy</label>
                                            <textarea name="shipping_policy" rows="4" class="form-control">{{ old('shipping_policy', $siteInfo->shipping_policy ?? '') }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Size Guide</label>
                                            <textarea name="size_guide" rows="6" class="form-control font-monospace" placeholder="Size | Chest | Waist | Hip&#10;S | 34–35 | 28–29 | 37–38&#10;M | 36–38 | 30–32 | 39–41">{{ old('size_guide', $siteInfo->size_guide ?? '') }}</textarea>
                                            <small class="text-muted">One row per line, columns separated by "|" (first line is the header) — shown as a table in the size-guide popup on every product. Leave blank to use the default chart.</small>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Delivery Charges ({{ \App\Support\Store::currency() }})</label>
                                                <input type="number" step="0.01" min="0" name="delivery_charges" class="form-control" value="{{ old('delivery_charges', isset($siteInfo) ? (float) $siteInfo->delivery_charges : 0) }}">
                                                <small class="text-muted">Added to every new order at checkout. Set 0 for free delivery. Changing this does not affect orders already placed.</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Free delivery over ({{ \App\Support\Store::currency() }})</label>
                                                <input type="number" step="1" min="0" name="free_shipping_threshold" class="form-control" value="{{ old('free_shipping_threshold', isset($siteInfo) && $siteInfo->free_shipping_threshold !== null ? (float) $siteInfo->free_shipping_threshold : '') }}" placeholder="e.g. 5000">
                                                <small class="text-muted">Orders at or above this amount (after discounts) ship free. Leave blank to always charge delivery.</small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Free Shipping Quote</label>
                                                <input type="text" name="free_shipping_quote" class="form-control" value="{{ old('free_shipping_quote', $siteInfo->free_shipping_quote ?? '') }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Gallery Quote</label>
                                                <input type="text" name="gallery_quote" class="form-control" value="{{ old('gallery_quote', $siteInfo->gallery_quote ?? '') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="tab-banners">
                                        <p class="text-muted small">Optional section banners shown across the storefront (not required for the site to work).</p>
                                        <div class="row">
                                            @foreach ([
                                                'home_banner' => 'Home', 'shop_banner' => 'Shop', 'about_banner' => 'About',
                                                'contact_banner' => 'Contact', 'faq_banner' => 'FAQ', 'checkout_banner' => 'Checkout',
                                                'cart_banner' => 'Cart', 'favorite_banner' => 'Favorites', 'privacy_banner' => 'Privacy',
                                                'return_banner' => 'Returns', 'account_banner' => 'Account', 'tnc_banner' => 'Terms',
                                            ] as $field => $label)
                                                <div class="col-md-3 mb-3">
                                                    <x-admin-image-field :field="$field" :label="$label . ' Banner'" :value="$siteInfo?->$field" max-height="70px" small />
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="tab-seo">
                                        <p class="text-muted small">How the homepage appears in Google and when the site link is shared (WhatsApp, Facebook). Other pages and products get their titles and descriptions automatically. Leave blank to use the brand name and tagline.</p>
                                        <div class="mb-3">
                                            <label class="form-label">Homepage Meta Title</label>
                                            <input type="text" name="meta_title" class="form-control" maxlength="255" value="{{ old('meta_title', $siteInfo->meta_title ?? '') }}" placeholder="e.g. {{ \App\Support\Store::brand()['name'] }} – Everyday Clothing in Pakistan">
                                            <small class="text-muted">Best under 60 characters.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Meta Description</label>
                                            <textarea name="meta_description" rows="3" class="form-control" maxlength="500" placeholder="One or two sentences about the store, shown under the title in Google">{{ old('meta_description', $siteInfo->meta_description ?? '') }}</textarea>
                                            <small class="text-muted">Best 120–160 characters. Also used for pages that have no description of their own.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Google Search Console Verification Code</label>
                                            <input type="text" name="google_site_verification" class="form-control" value="{{ old('google_site_verification', $siteInfo->google_site_verification ?? '') }}" placeholder="Only the content=&quot;…&quot; value from the HTML tag method">
                                            <small class="text-muted">From Search Console → Add property → HTML tag. Paste just the code, not the whole &lt;meta&gt; tag.</small>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="tab-gallery">
                                        <p class="text-muted small">Images and short videos both work here — each can link anywhere when clicked on the homepage (leave its link blank to send it to the Shop page instead). Added, edited, and removed immediately below (no need to press "Save Site Info").</p>

                                        <div class="row align-items-end mb-4 pb-4 border-bottom">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">New Image / Video</label>
                                                <div class="admin-image-preview d-none">
                                                    <img id="galleryImagePreview" class="img-fluid rounded d-block bg-dark p-2" style="max-height:120px;">
                                                    <video id="galleryVideoPreview" class="img-fluid rounded d-block bg-dark p-2 d-none" style="max-height:120px;" muted controls></video>
                                                </div>
                                                <input type="file" id="galleryImageInput" accept="image/*,video/mp4,video/quicktime,video/webm" class="form-control mt-2">
                                                <small class="text-muted">Images, or MP4/MOV/WebM video up to 20MB.</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">CTA Link</label>
                                                <input type="text" id="galleryCtaLinkInput" class="form-control" placeholder="/collections/all (default: Shop)">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <button type="button" id="galleryAddBtn" class="btn btn-primary w-100">Add Image / Video</button>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap gap-3" data-gallery-list>
                                            @foreach ($data['gallery'] as $image)
                                                <div class="admin-image-preview" style="width:150px;" data-gallery-item="{{ $image->id }}">
                                                    @if ($image->isVideo())
                                                        <video src="{{ $image->image }}" style="width:150px;height:150px;object-fit:cover;" class="rounded d-block" muted controls></video>
                                                    @else
                                                        <img src="{{ $image->image }}" style="width:150px;height:150px;object-fit:cover;" class="rounded d-block">
                                                    @endif
                                                    <button type="button" class="admin-image-remove" data-gallery-id="{{ $image->id }}" title="Remove item">&times;</button>
                                                    <input type="text" class="form-control form-control-sm mt-2" data-gallery-link-input="{{ $image->id }}" value="{{ $image->cta_link }}" placeholder="/collections/all (default: Shop)">
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted d-block mt-2" data-gallery-hint @if ($data['gallery']->isEmpty()) style="display:none;" @endif>Click × to remove an image or video. A CTA link saves automatically when you click away from its field.</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">Save Site Info</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')

<script>
    function previewFileInto(inputId, imgId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                const img = document.getElementById(imgId);
                reader.onload = (e) => { img.src = e.target.result; img.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    previewFileInto('logoInput', 'previewLogo');
    previewFileInto('promoLeftInput', 'previewPromoLeft');
    previewFileInto('promoFullInput', 'previewPromoFull');
    previewFileInto('promoRightInput', 'previewPromoRight');

    // Gallery's file input takes images AND videos, so it needs its own
    // handler (previewFileInto only ever swaps a single <img>'s src) —
    // shows whichever of the two preview elements matches the picked
    // file's type and hides the other.
    (function () {
        var input = document.getElementById('galleryImageInput');
        var img = document.getElementById('galleryImagePreview');
        var video = document.getElementById('galleryVideoPreview');
        if (!input || !img || !video) return;

        input.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;

            if (file.type.indexOf('video/') === 0) {
                video.src = URL.createObjectURL(file);
                video.classList.remove('d-none');
                img.classList.add('d-none');
            } else {
                var reader = new FileReader();
                reader.onload = function (e) { img.src = e.target.result; };
                reader.readAsDataURL(file);
                img.classList.remove('d-none');
                video.classList.add('d-none');
            }

            input.closest('.mb-3')?.querySelector('.admin-image-preview')?.classList.remove('d-none');
        });
    })();

    // Reveal a field's preview wrapper (and its × button) as soon as a
    // new file is chosen for it — it starts hidden via .d-none when the
    // field has no saved image yet.
    document.querySelectorAll('.admin-image-preview img[id]').forEach(function (img) {
        var input = img.closest('.mb-3')?.querySelector('input[type="file"]');
        if (!input) return;
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                img.closest('.admin-image-preview').classList.remove('d-none');
            }
        });
    });

    // Delete-image (×) buttons — logo/promo/section banners post a
    // field name to one endpoint; gallery images post their own id to
    // another (each gallery image is its own row, so removing one never
    // touches the rest). Both remove the element from view immediately
    // on success rather than waiting for a full page reload.
    var csrfToken = document.querySelector('input[name="_token"]').value;

    function deleteSiteInfoImage(url, body, onSuccess) {
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(body || {}),
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    onSuccess();
                } else {
                    alert(data.message || 'Could not delete that image.');
                }
            })
            .catch(function () {
                alert('Could not delete that image — please try again.');
            });
    }

    // Add Gallery Image — its own fetch POST (not the page's big "Save
    // Site Info" form: nesting a second <form> inside that one isn't
    // valid HTML) so a new image+link appears in the grid immediately,
    // the same instant-feedback pattern the delete button already uses.
    document.getElementById('galleryAddBtn')?.addEventListener('click', function () {
        var fileInput = document.getElementById('galleryImageInput');
        var linkInput = document.getElementById('galleryCtaLinkInput');
        var file = fileInput.files && fileInput.files[0];

        if (!file) {
            alert('Choose an image or video first.');
            return;
        }

        var formData = new FormData();
        formData.append('image', file);
        formData.append('cta_link', linkInput.value);

        fetch('{{ route('admin.siteinfo.gallery.store') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData,
        })
            .then(function (response) {
                return response.json().then(function (data) { return { ok: response.ok, data: data }; });
            })
            .then(function (result) {
                if (!result.ok || !result.data.success) {
                    var firstError = result.data.errors ? Object.values(result.data.errors)[0][0] : null;
                    alert(result.data.message || firstError || 'Could not add that image.');
                    return;
                }

                var item = result.data.item;
                var mediaTag = item.type === 'video'
                    ? '<video src="' + item.image + '" style="width:150px;height:150px;object-fit:cover;" class="rounded d-block" muted controls></video>'
                    : '<img src="' + item.image + '" style="width:150px;height:150px;object-fit:cover;" class="rounded d-block">';

                var wrapper = document.createElement('div');
                wrapper.className = 'admin-image-preview';
                wrapper.style.width = '150px';
                wrapper.setAttribute('data-gallery-item', item.id);
                wrapper.innerHTML = mediaTag +
                    '<button type="button" class="admin-image-remove" data-gallery-id="' + item.id + '" title="Remove item">&times;</button>' +
                    '<input type="text" class="form-control form-control-sm mt-2" data-gallery-link-input="' + item.id + '" value="' + (item.cta_link || '') + '" placeholder="/collections/all (default: Shop)">';
                document.querySelector('[data-gallery-list]').appendChild(wrapper);

                var hint = document.querySelector('[data-gallery-hint]');
                if (hint) hint.style.display = '';

                fileInput.value = '';
                linkInput.value = '';

                var previewImg = document.getElementById('galleryImagePreview');
                var previewVideo = document.getElementById('galleryVideoPreview');
                previewImg.closest('.admin-image-preview').classList.add('d-none');
                previewImg.src = '';
                previewVideo.src = '';
                previewImg.classList.remove('d-none');
                previewVideo.classList.add('d-none');
            })
            .catch(function () {
                alert('Could not add that image or video — please try again.');
            });
    });

    // Per-image CTA link — saves on blur/change via one delegated
    // listener, so it also covers images added to the grid above after
    // page load (no per-element listener to re-bind).
    document.querySelector('[data-gallery-list]')?.addEventListener('change', function (e) {
        var input = e.target.closest('[data-gallery-link-input]');
        if (!input) return;

        var galleryId = input.getAttribute('data-gallery-link-input');

        fetch('{{ url('admin/site-info/gallery') }}/' + galleryId, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cta_link: input.value }),
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) alert(data.message || 'Could not save that link.');
            })
            .catch(function () {
                alert('Could not save that link — please try again.');
            });
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.admin-image-remove');
        if (!btn) return;

        if (!confirm('Delete this image? This cannot be undone.')) return;

        var galleryId = btn.getAttribute('data-gallery-id');

        if (galleryId) {
            deleteSiteInfoImage('{{ url('admin/site-info/gallery') }}/' + galleryId, null, function () {
                var item = btn.closest('[data-gallery-item]');
                item.remove();
                if (!document.querySelector('[data-gallery-item]')) {
                    var hint = document.querySelector('[data-gallery-hint]');
                    if (hint) hint.style.display = 'none';
                }
            });
            return;
        }

        var field = btn.getAttribute('data-field');
        deleteSiteInfoImage('{{ route('admin.siteinfo.image.delete') }}', { field: field }, function () {
            var wrap = btn.closest('.admin-image-preview');
            wrap.classList.add('d-none');
            var img = wrap.querySelector('img');
            img.src = '';
            var input = wrap.closest('.mb-3').querySelector('input[type="file"]');
            if (input) input.value = '';
        });
    });
</script>

<style>
    .admin-image-preview {
        position: relative;
        display: inline-block;
        margin-bottom: 8px;
    }
    .admin-image-remove {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 22px;
        height: 22px;
        line-height: 20px;
        padding: 0;
        border-radius: 50%;
        background: #dc3545;
        color: #fff;
        border: 2px solid #fff;
        font-size: 14px;
        cursor: pointer;
        z-index: 2;
    }
</style>
