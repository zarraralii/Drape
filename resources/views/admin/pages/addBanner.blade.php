@include('admin.layouts.header', ['title' => isset($data['banner']) ? 'Edit Banner' : 'Add Banner'])
@include('admin.layouts.menu')

@php($banner = $data['banner'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $banner ? 'Edit Banner' : 'Add Banner' }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-banner') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @if ($banner)
                                    <input type="hidden" name="id" value="{{ $banner->id }}">
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Eyebrow (small text above headline)</label>
                                        <input type="text" name="eyebrow" class="form-control" value="{{ old('eyebrow', $banner->eyebrow ?? '') }}" placeholder="Summer edit">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Order</label>
                                        <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $banner->sort_order ?? 0) }}">
                                        <small class="text-muted">Lower numbers show first.</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Headline</label>
                                    <input type="text" name="headline" class="form-control" value="{{ old('headline', $banner->headline ?? '') }}" required placeholder="Light layers for long days">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Linen, lawn and breezy cotton — new pieces made to move with you...">{{ old('description', $banner->description ?? '') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Button Label</label>
                                        <input type="text" name="cta_label" class="form-control" value="{{ old('cta_label', $banner->cta_label ?? '') }}" placeholder="Shop New Arrivals">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Button Link</label>
                                        <input type="text" name="cta_link" class="form-control" value="{{ old('cta_link', $banner->cta_link ?? '') }}" placeholder="/collections/all">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Slide Image (fallback)</label>
                                        @if ($banner?->image)
                                            <img src="{{ $banner->image }}" class="img-fluid rounded mb-2 d-block" style="max-height:180px;">
                                        @endif
                                        <input type="file" name="image" accept="image/*" class="form-control">
                                        <small class="text-muted">Used as the slide background only when no wide background image is set.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Background Image</label>
                                        @if ($banner?->background_image)
                                            <img src="{{ $banner->background_image }}" data-original="{{ $banner->background_image }}" id="previewBackground" class="img-fluid rounded mb-2 d-block" style="max-height:180px;">
                                        @endif
                                        <input type="file" name="background_image" accept="image/*" data-image-spec="banner_background" data-preview="#previewBackground" class="form-control">
                                        <small class="text-muted">Optional. Fills the full width of the slide behind the text. Must match the website slide shape ({{ config('media.banner_background.width') }} × {{ config('media.banner_background.height') }} px). Minimum {{ config('media.banner_background.min_width') }} × {{ config('media.banner_background.min_height') }} px, recommended {{ config('media.banner_background.recommended') }}.</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">{{ $banner ? 'Update Banner' : 'Add Banner' }}</button>
                                <a href="{{ route('admin.banners') }}" class="btn btn-light">Cancel</a>
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
