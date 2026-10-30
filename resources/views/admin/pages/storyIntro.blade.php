@include('admin.layouts.header', ['title' => 'Our Story Intro'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Our Story — Page Intro</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">The centred "Chapter One" intro at the top of the Our Story page, above the sections. Leave a field empty to use the default text.</p>

                            <form action="{{ route('admin.story-intro') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Eyebrow (small text above the headline)</label>
                                        <input type="text" name="story_eyebrow" class="form-control" value="{{ old('story_eyebrow', $siteInfo->story_eyebrow ?? 'Chapter One') }}" placeholder="Chapter One">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Headline</label>
                                        <input type="text" name="story_headline" class="form-control" value="{{ old('story_headline', $siteInfo->story_headline ?? 'Clothes that hang right and last') }}" placeholder="Clothes that hang right and last">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="story_description" rows="4" class="form-control" placeholder="Drape started with a simple idea...">{{ old('story_description', $siteInfo->story_description ?? '') }}</textarea>
                                </div>

                                <h6 class="mt-4 mb-2">Button at the end of the page</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Button Label</label>
                                        <input type="text" name="story_cta_label" class="form-control" value="{{ old('story_cta_label', $siteInfo->story_cta_label ?? 'Explore the Collection') }}" placeholder="Explore the Collection">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Button Link</label>
                                        <input type="text" name="story_cta_link" class="form-control" value="{{ old('story_cta_link', $siteInfo->story_cta_link ?? '') }}" placeholder="/collections/all">
                                        <small class="text-muted">Leave empty to link to the Shop page.</small>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Save Intro</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
