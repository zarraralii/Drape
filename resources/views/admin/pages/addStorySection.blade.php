@include('admin.layouts.header', ['title' => isset($data['section']) ? 'Edit Story Section' : 'Add Story Section'])
@include('admin.layouts.menu')

@php($section = $data['section'] ?? null)
@php($spec = config('media.story_section'))

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $section ? 'Edit Story Section' : 'Add Story Section' }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-story-section') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @if ($section)
                                    <input type="hidden" name="id" value="{{ $section->id }}">
                                @endif

                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" value="{{ old('title', $section->title ?? '') }}" required placeholder="Where It All Began">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Order</label>
                                        <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $section->sort_order ?? 0) }}">
                                        <small class="text-muted">Lower numbers show first.</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="5">{{ old('description', $section->description ?? '') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Image</label>
                                    @if ($section?->image)
                                        <img src="{{ $section->image }}" data-original="{{ $section->image }}" id="previewStoryImage" class="img-fluid rounded mb-2 d-block" style="max-height:180px;">
                                    @else
                                        <img src="" id="previewStoryImage" class="img-fluid rounded mb-2 d-block" style="max-height:180px; display:none;">
                                    @endif
                                    <input type="file" name="image" id="storyImageInput" accept="image/*" data-image-spec="story_section" data-preview="#previewStoryImage" class="form-control" {{ $section ? '' : 'required' }}>
                                    <small class="text-muted d-block">Must match the website frame shape ({{ $spec['width'] }} × {{ $spec['height'] }} px, landscape). Minimum {{ $spec['min_width'] }} × {{ $spec['min_height'] }} px, recommended {{ $spec['recommended'] }}.</small>
                                </div>

                                <h6 class="mt-4 mb-2">Button on the image (optional)</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Button Label</label>
                                        <input type="text" name="cta_label" class="form-control" value="{{ old('cta_label', $section->cta_label ?? '') }}" placeholder="Explore the Collection">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Button Link</label>
                                        <input type="text" name="cta_link" class="form-control" value="{{ old('cta_link', $section->cta_link ?? '') }}" placeholder="/collections/all">
                                        <small class="text-muted">The whole image also links here.</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">{{ $section ? 'Update Section' : 'Add Section' }}</button>
                                <a href="{{ route('admin.story-sections') }}" class="btn btn-light">Cancel</a>
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

<script>
    document.getElementById('storyImageInput').addEventListener('change', function () {
        const img = document.getElementById('previewStoryImage');
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => { img.src = e.target.result; img.style.display = 'block'; };
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
