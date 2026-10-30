@include('admin.layouts.header', ['title' => isset($data['category']) ? 'Edit Collection' : 'Add Collection'])
@include('admin.layouts.menu')

@php($category = $data['category'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $category ? 'Edit Collection' : 'Add Collection' }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-category') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @if ($category)
                                    <input type="hidden" name="id" value="{{ $category->id }}">
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Parent Collection (optional)</label>
                                        <select name="parent_id" class="form-select">
                                            <option value="">None (top-level)</option>
                                            @foreach ($data['categories'] as $option)
                                                @if (!$category || $option->id !== $category->id)
                                                    <option value="{{ $option->id }}" {{ old('parent_id', $category->parent_id ?? '') == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">URL handle</label>
                                        <div class="input-group">
                                            <span class="input-group-text">/collections/</span>
                                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug ?? '') }}" placeholder="auto from name">
                                        </div>
                                        <small class="text-muted">Changing this changes the collection's web address.</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Menu order</label>
                                        <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                                        <small class="text-muted">Lower numbers show first.</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="3" class="form-control" placeholder="Shown under the collection title">{{ old('description', $category->description ?? '') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Cover Image</label>
                                        @if ($category?->cover_image)
                                            <img src="{{ $category->cover_image }}" class="img-fluid rounded mb-2 d-block" style="max-height:140px;">
                                        @endif
                                        <input type="file" name="cover_image" accept="image/*" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Banner Image</label>
                                        @if ($category?->banner_image)
                                            <img src="{{ $category->banner_image }}" class="img-fluid rounded mb-2 d-block" style="max-height:140px;">
                                        @endif
                                        <input type="file" name="banner_image" accept="image/*" class="form-control">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">{{ $category ? 'Update Collection' : 'Add Collection' }}</button>
                                <a href="{{ route('admin.category') }}" class="btn btn-light">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
