@include('admin.layouts.header', ['title' => isset($data['product']) ? 'Edit Product' : 'Add Product'])
@include('admin.layouts.menu')

@php
    $product = $data['product'] ?? null;
    $variations = $data['variations'] ?? collect();
    // Rows for the variant builder: the submitted ones after a validation
    // error, otherwise the product's saved variants.
    $variantRows = old('variants')
        ? collect(old('variants'))->map(fn ($row) => ['id' => $row['id'] ?? null, 'options' => json_decode($row['options'] ?? '{}', true) ?: [], 'sku' => $row['sku'] ?? '', 'price' => $row['price'] ?? '', 'quantity' => $row['quantity'] ?? 0, 'image' => null])
        : $variations->map(fn ($v) => ['id' => $v->id, 'options' => $v->optionValues(), 'sku' => $v->sku, 'price' => $v->price, 'quantity' => $v->quantity, 'image' => $v->image]);
@endphp

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $product ? 'Edit Product' : 'Add Product' }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-product') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @if ($product)
                                    <input type="hidden" name="id" value="{{ $product->id }}">
                                @endif

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Collection</label>
                                                <select name="category_id" class="form-select" required>
                                                    <option value="">Select…</option>
                                                    @foreach ($data['categories'] as $category)
                                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Options</label>
                                                <select name="type" id="productType" class="form-select">
                                                    <option value="variable" {{ old('type', $product->type ?? 'variable') === 'variable' ? 'selected' : '' }}>Has sizes / colours</option>
                                                    <option value="simple" {{ old('type', $product->type ?? 'variable') === 'simple' ? 'selected' : '' }}>Single item (one size)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Published Date</label>
                                                <input type="text" id="published_date" name="published_date" class="form-control" value="{{ old('published_date', $product?->published_date ? \Carbon\Carbon::parse($product->published_date)->format('Y-m-d') : now()->toDateString()) }}" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Price ({{ \App\Support\Store::currency() }})</label>
                                                <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $product->price ?? '') }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3" id="qtyWrap">
                                                <label class="form-label">Quantity in Stock</label>
                                                <input type="number" min="0" name="qty" class="form-control" value="{{ old('qty', $product->qty ?? 0) }}">
                                                <small class="text-muted">Leave blank for unlimited stock.</small>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_discounted" name="is_discounted" value="1" {{ old('is_discounted', $product->is_discounted ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_discounted">On sale (show a cut / discounted price)</label>
                                        </div>

                                        <div class="row" id="discountFields" style="{{ old('is_discounted', $product->is_discounted ?? false) ? '' : 'display:none;' }}">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Discounted Price ({{ \App\Support\Store::currency() }})</label>
                                                <input type="number" step="0.01" min="0" name="discounted_price" class="form-control" value="{{ old('discounted_price', $product->discounted_price ?? '') }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Discount Starts</label>
                                                <input type="text" id="discount_start_date" name="discount_start_date" class="form-control" value="{{ old('discount_start_date', $product?->discount_start_date ? \Carbon\Carbon::parse($product->discount_start_date)->format('Y-m-d') : '') }}" placeholder="Optional">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Discount Ends</label>
                                                <input type="text" id="discount_end_date" name="discount_end_date" class="form-control" value="{{ old('discount_end_date', $product?->discount_end_date ? \Carbon\Carbon::parse($product->discount_end_date)->format('Y-m-d') : '') }}" placeholder="Optional">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Short Description</label>
                                            <textarea id="short_description" name="short_description" rows="3" class="form-control">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                                            <small class="text-muted">Shown with its formatting on the product page, and as plain text on product cards.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Full Description</label>
                                            <textarea id="description" name="description" rows="5" class="form-control">{{ old('description', $product->description ?? '') }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Fabric &amp; Care</label>
                                            <textarea name="material_care" rows="3" class="form-control" placeholder="100% cotton lawn&#10;Machine wash cold&#10;Iron on reverse">{{ old('material_care', $product->material_care ?? '') }}</textarea>
                                            <small class="text-muted">One point per line. Shown in its own section on the product page.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tags</label>
                                            {{-- Text input, not <select multiple>: Choices.js v9 only lets you
                                            type new free-text items into a text input. It keeps the value
                                            as a comma-separated string, split back into an array on save. --}}
                                            @php($tagsValue = old('tags', $product->tags ?? []))
                                            <input type="text" id="tags" name="tags" class="form-control"
                                                value="{{ is_array($tagsValue) ? implode(',', $tagsValue) : $tagsValue }}" placeholder="Type a tag and press enter">

                                            <small class="text-muted">Press enter/comma to add a tag.</small>
                                        </div>

                                        <h5 class="mt-4">SEO <small class="text-muted fw-normal">(optional)</small></h5>
                                        <p class="text-muted small">How this product appears in Google and when shared on WhatsApp / Facebook. Leave blank to use the product name and short description.</p>
                                        <div class="mb-3">
                                            <label class="form-label">Meta Title</label>
                                            <input type="text" name="meta_title" class="form-control" maxlength="255" value="{{ old('meta_title', $product->meta_title ?? '') }}" placeholder="e.g. Relaxed Linen Shirt – Breathable Summer Shirt">
                                            <small class="text-muted">Best under 60 characters. The brand name is added automatically.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Meta Description</label>
                                            <textarea name="meta_description" rows="2" class="form-control" maxlength="500" placeholder="One or two sentences shown under the title in Google results">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                                            <small class="text-muted">Best 120–160 characters.</small>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">Featured on homepage (top 4 shown there)</label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_deal" name="is_deal" value="1" {{ old('is_deal', $product->is_deal ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_deal">Mark as Deal / Bundle</label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Cover Image</label>
                                            @if ($product?->cover_image)
                                                <img src="{{ $product->cover_image }}" data-original="{{ $product->cover_image }}" id="previewImage" class="img-fluid rounded mb-2 d-block" style="max-height:160px;">
                                            @else
                                                <img src="" id="previewImage" class="img-fluid rounded mb-2 d-block" style="max-height:160px; display:none;">
                                            @endif
                                            <input type="file" name="cover_image" id="imageInput" accept="image/*" data-image-spec="product" data-preview="#previewImage" class="form-control" {{ $product ? '' : 'required' }}>
                                            <small class="text-muted d-block">Must match the website frame shape ({{ config('media.product.width') }} × {{ config('media.product.height') }} px, portrait). Minimum {{ config('media.product.min_width') }} × {{ config('media.product.min_height') }} px, recommended {{ config('media.product.recommended') }}.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Hover Image (optional)</label>
                                            @if ($product?->getRawOriginal('hover_image'))
                                                <img src="{{ $product->hover_image }}" data-original="{{ $product->hover_image }}" id="previewHoverImage" class="img-fluid rounded mb-2 d-block" style="max-height:160px;">
                                            @else
                                                <img src="" id="previewHoverImage" class="img-fluid rounded mb-2 d-block" style="max-height:160px; display:none;">
                                            @endif
                                            <input type="file" name="hover_image" id="hoverImageInput" accept="image/*" data-image-spec="product" data-preview="#previewHoverImage" class="form-control">
                                            <small class="text-muted">Shown when a visitor hovers the card. Same size as the cover image.</small>
                                        </div>

                                        @php($maxGallery = \App\Http\Controllers\Admin\ProductController::MAX_GALLERY_IMAGES)
                                        <div class="mb-3" id="galleryField" data-max="{{ $maxGallery }}">
                                            <label class="form-label d-flex justify-content-between">
                                                <span>Additional Images</span>
                                                <small class="text-muted"><span id="galleryCount">{{ $product?->gallery?->count() ?? 0 }}</span> / {{ $maxGallery }}</small>
                                            </label>

                                            @if ($product?->gallery?->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                    @foreach ($product->gallery as $image)
                                                        <label class="gallery-thumb" title="Click to remove / keep">
                                                            <input type="checkbox" name="deleted_images[]" value="{{ $image->id }}" class="d-none gallery-remove">
                                                            <img src="{{ $image->image }}" alt="">
                                                            <span class="gallery-thumb-x"><i class="mdi mdi-close"></i></span>
                                                            <span class="gallery-thumb-removed">Will be removed</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <small class="text-muted d-block mb-2">Click an image to remove it when you save (click again to keep it).</small>
                                            @endif

                                            <div class="d-flex flex-wrap gap-2 mb-2" id="galleryNewPreviews"></div>
                                            <input type="file" name="additional_images[]" id="galleryInput" accept="image/*" data-image-spec="product" class="form-control" multiple>
                                            <small class="text-muted d-block">Shown as extra photos on the product page. Same size as the cover image, up to {{ $maxGallery }} images.</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Variant builder (only when the product has sizes / colours) --}}
                                <div id="variationsSection">
                                    <hr>
                                    <h5 class="mb-1">Sizes &amp; colours</h5>
                                    <p class="text-muted small">Tick the colours and sizes this product comes in, then press <strong>Build variants</strong>. Each combination gets its own stock, optional price (blank = product price) and optional photo — a photo on any one colour is shown when shoppers pick that colour. Manage the list of colours and sizes under <a href="{{ route('admin.attributes') }}">Attributes</a>.</p>

                                    @if ($data['attributes']->isEmpty())
                                        <div class="alert alert-warning">No options yet. <a href="{{ route('admin.add-attribute') }}">Add a "Color" and a "Size" option</a> first (or run <code>php artisan db:seed</code>).</div>
                                    @endif

                                    <div class="row" id="optionPickers">
                                        @foreach ($data['attributes'] as $attribute)
                                            @php($used = $variantRows->pluck('options.' . $attribute->name)->filter()->unique()->values()->all())
                                            <div class="col-md-6 mb-3" data-option-picker="{{ $attribute->name }}">
                                                <label class="form-label d-flex justify-content-between">
                                                    <span>{{ $attribute->name }}</span>
                                                    <a href="#" class="small" data-toggle-all>Select all</a>
                                                </label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($attribute->values as $value)
                                                        <label class="option-chip">
                                                            <input type="checkbox" value="{{ $value->value }}" @checked(in_array($value->value, $used, true))>
                                                            <span>
                                                                @if ($value->swatch)<i style="background: {{ $value->swatch }}"></i>@endif
                                                                {{ $value->value }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                        <button type="button" id="buildVariants" class="btn btn-outline-primary btn-sm">Build variants</button>
                                        <span class="text-muted small ms-2">Set stock for all:</span>
                                        <input type="number" min="0" id="bulkStock" class="form-control form-control-sm" style="width:90px">
                                        <button type="button" id="applyBulkStock" class="btn btn-light btn-sm">Apply</button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle" id="variantTable">
                                            <thead>
                                                <tr><th>Variant</th><th style="width:170px">SKU</th><th style="width:140px">Price</th><th style="width:110px">Stock</th><th style="width:250px">Photo</th><th></th></tr>
                                            </thead>
                                            <tbody id="variantRows">
                                                @foreach ($variantRows as $i => $row)
                                                    <tr class="variant-row" data-signature="{{ collect($row['options'])->sortKeys()->toJson() }}">
                                                        <td>
                                                            <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $row['id'] }}">
                                                            <input type="hidden" name="variants[{{ $i }}][options]" value="{{ json_encode($row['options']) }}">
                                                            <strong>{{ implode(' / ', $row['options']) }}</strong>
                                                        </td>
                                                        <td><input type="text" name="variants[{{ $i }}][sku]" class="form-control form-control-sm" value="{{ $row['sku'] }}" placeholder="auto"></td>
                                                        <td><input type="number" step="0.01" min="0" name="variants[{{ $i }}][price]" class="form-control form-control-sm" value="{{ $row['price'] }}" placeholder="same"></td>
                                                        <td><input type="number" min="0" name="variants[{{ $i }}][quantity]" class="form-control form-control-sm variant-qty" value="{{ $row['quantity'] }}" required></td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                @if ($row['image'])<img src="{{ $row['image'] }}" alt="" style="width:32px;height:42px;object-fit:cover;border-radius:3px">@endif
                                                                <input type="file" name="variants[{{ $i }}][image]" accept="image/*" class="form-control form-control-sm">
                                                            </div>
                                                        </td>
                                                        <td><button type="button" class="btn btn-outline-danger btn-sm remove-variation" title="Remove">✕</button></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="text-muted small mb-0" id="variantEmpty" @if ($variantRows->isNotEmpty()) style="display:none" @endif>No variants yet — tick some options above and press Build variants.</p>
                                    @error('variants')<div class="text-danger small">{{ $message }}</div>@enderror
                                    <div id="deletedVariationsFields"></div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">{{ $product ? 'Update Product' : 'Add Product' }}</button>
                                    <a href="{{ route('admin.products') }}" class="btn btn-light">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
@include('admin.partials.image-spec-check')

<style>
    .gallery-thumb { position: relative; width: 72px; height: 72px; margin: 0; cursor: pointer; }
    .gallery-thumb img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid #e5e5e5; }
    .gallery-thumb-x { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: #f46a6a; color: #fff; font-size: 13px; line-height: 20px; text-align: center; }
    .gallery-thumb-removed { display: none; position: absolute; inset: 0; border-radius: 6px; background: rgba(244, 106, 106, .8); color: #fff; font-size: 10px; font-weight: 600; text-align: center; align-items: center; justify-content: center; line-height: 1.2; padding: 4px; }
    .gallery-thumb.is-removed .gallery-thumb-removed { display: flex; }
    .gallery-thumb.is-removed .gallery-thumb-x { display: none; }
    .gallery-new { position: relative; width: 72px; height: 72px; }
    .gallery-new img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 2px solid #34c38f; }
    .option-chip input { display: none; }
    .option-chip span { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border: 1px solid #d5d7db; border-radius: 20px; font-size: 13px; cursor: pointer; user-select: none; }
    .option-chip span i { width: 14px; height: 14px; border-radius: 50%; box-shadow: inset 0 0 0 1px rgba(0,0,0,.15); }
    .option-chip input:checked + span { background: #1b1a18; border-color: #1b1a18; color: #fff; }
    .gallery-new span { position: absolute; bottom: 2px; left: 2px; background: #34c38f; color: #fff; font-size: 9px; padding: 0 4px; border-radius: 3px; }
</style>

<script>
    // Additional images: toggle existing ones for removal, preview newly
    // picked files, and keep a live "n / max" count so the admin sees the
    // cap before the server rejects it.
    (function () {
        const field = document.getElementById('galleryField');
        if (!field) return;

        const max = parseInt(field.dataset.max, 10);
        const input = document.getElementById('galleryInput');
        const previews = document.getElementById('galleryNewPreviews');
        const countEl = document.getElementById('galleryCount');

        const keptCount = () => field.querySelectorAll('.gallery-remove:not(:checked)').length;

        function render() {
            previews.innerHTML = '';
            Array.from(input.files || []).forEach((file) => {
                const box = document.createElement('div');
                box.className = 'gallery-new';
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);
                const tag = document.createElement('span');
                tag.textContent = 'New';
                box.append(img, tag);
                previews.appendChild(box);
            });
            countEl.textContent = keptCount() + (input.files ? input.files.length : 0);
        }

        field.querySelectorAll('.gallery-remove').forEach((checkbox) => {
            checkbox.addEventListener('change', function () {
                this.closest('.gallery-thumb').classList.toggle('is-removed', this.checked);
                render();
            });
        });

        input.addEventListener('change', function () {
            const total = keptCount() + this.files.length;
            if (total > max) {
                this.value = '';
                Swal.fire({ icon: 'error', title: 'Too many images', text: `A product can have at most ${max} additional images. You picked ${total - keptCount()} with ${keptCount()} already saved.` });
            }
            render();
        });

        // image-spec-check clears the input when a file is the wrong size.
        input.addEventListener('image-spec-cleared', render);
    })();
</script>

<script>
    function previewInto(inputEl, imgEl) {
        inputEl.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => { imgEl.src = e.target.result; imgEl.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    previewInto(document.getElementById('imageInput'), document.getElementById('previewImage'));
    previewInto(document.getElementById('hoverImageInput'), document.getElementById('previewHoverImage'));

    document.getElementById('is_discounted').addEventListener('change', function () {
        document.getElementById('discountFields').style.display = this.checked ? '' : 'none';
    });

    document.getElementById('productType').addEventListener('change', function () {
        const isVariable = this.value === 'variable';
        document.getElementById('variationsSection').style.display = isVariable ? '' : 'none';
        document.getElementById('qtyWrap').style.display = isVariable ? 'none' : '';
    });
    document.getElementById('productType').dispatchEvent(new Event('change'));

    // Variant builder: every combination of the ticked option values
    // becomes a row; rows that already exist (same options) are kept.
    (function () {
        const tbody = document.getElementById('variantRows');
        const empty = document.getElementById('variantEmpty');
        let nextIndex = {{ $variantRows->count() }};
        const escapeHtml = (v) => String(v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const signature = (options) => JSON.stringify(Object.fromEntries(Object.keys(options).sort().map((k) => [k, options[k]])));

        function combinations(groups) {
            return groups.reduce((acc, [name, values]) => acc.flatMap((combo) => values.map((v) => ({ ...combo, [name]: v }))), [{}]);
        }

        function rowHtml(options, i) {
            const json = escapeHtml(JSON.stringify(options));
            return `<td><input type="hidden" name="variants[${i}][options]" value="${json}"><strong>${escapeHtml(Object.values(options).join(' / '))}</strong> <span class="badge bg-success-subtle text-success">new</span></td>
                <td><input type="text" name="variants[${i}][sku]" class="form-control form-control-sm" placeholder="auto"></td>
                <td><input type="number" step="0.01" min="0" name="variants[${i}][price]" class="form-control form-control-sm" placeholder="same"></td>
                <td><input type="number" min="0" name="variants[${i}][quantity]" class="form-control form-control-sm variant-qty" value="0" required></td>
                <td><input type="file" name="variants[${i}][image]" accept="image/*" class="form-control form-control-sm"></td>
                <td><button type="button" class="btn btn-outline-danger btn-sm remove-variation" title="Remove">✕</button></td>`;
        }

        document.getElementById('buildVariants').addEventListener('click', function () {
            const groups = [...document.querySelectorAll('[data-option-picker]')]
                .map((el) => [el.dataset.optionPicker, [...el.querySelectorAll('input:checked')].map((i) => i.value)])
                .filter(([, values]) => values.length);

            if (!groups.length) {
                Swal.fire({ icon: 'info', title: 'Pick some options', text: 'Tick at least one size or colour first.' });
                return;
            }

            const existing = new Set([...tbody.querySelectorAll('.variant-row')].map((r) => r.dataset.signature));
            let added = 0;
            combinations(groups).forEach((options) => {
                const sig = signature(options);
                if (existing.has(sig)) return;
                const tr = document.createElement('tr');
                tr.className = 'variant-row';
                tr.dataset.signature = sig;
                tr.innerHTML = rowHtml(options, nextIndex++);
                tbody.appendChild(tr);
                added++;
            });
            empty.style.display = tbody.children.length ? 'none' : '';
            Swal.fire({ icon: 'success', title: added ? added + ' variant(s) added' : 'Nothing new to add', toast: true, position: 'top-end', showConfirmButton: false, timer: 2200 });
        });

        document.querySelectorAll('[data-toggle-all]').forEach((link) => link.addEventListener('click', function (e) {
            e.preventDefault();
            const boxes = [...this.closest('[data-option-picker]').querySelectorAll('input')];
            const check = boxes.some((b) => !b.checked);
            boxes.forEach((b) => { b.checked = check; });
        }));

        document.getElementById('applyBulkStock').addEventListener('click', function () {
            const value = document.getElementById('bulkStock').value;
            if (value === '') return;
            tbody.querySelectorAll('.variant-qty').forEach((input) => { input.value = value; });
        });

        document.addEventListener('click', function (e) {
            if (!e.target.classList.contains('remove-variation')) return;
            const row = e.target.closest('.variant-row');
            const hiddenId = row.querySelector('input[name$="[id]"]');
            if (hiddenId && hiddenId.value) {
                const field = document.createElement('input');
                field.type = 'hidden';
                field.name = 'deleted_variants[]';
                field.value = hiddenId.value;
                document.getElementById('deletedVariationsFields').appendChild(field);
            }
            row.remove();
            empty.style.display = tbody.children.length ? 'none' : '';
        });
    })();
</script>
