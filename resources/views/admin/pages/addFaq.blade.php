@include('admin.layouts.header', ['title' => isset($data['faq']) ? 'Edit FAQ' : 'Add FAQ'])
@include('admin.layouts.menu')

@php($faq = $data['faq'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $faq ? 'Edit FAQ' : 'Add FAQ' }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-faq') }}" method="POST">
                                @csrf
                                @if ($faq)
                                    <input type="hidden" name="id" value="{{ $faq->id }}">
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Question</label>
                                    <input type="text" name="question" class="form-control" value="{{ old('question', $faq->question ?? '') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Answer</label>
                                    <textarea id="answer" name="answer" rows="5" class="form-control">{{ old('answer', $faq->answer ?? '') }}</textarea>
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort" class="form-control" value="{{ old('sort', $faq->sort ?? 999) }}">
                                </div>

                                <button type="submit" class="btn btn-primary">{{ $faq ? 'Update FAQ' : 'Save FAQ' }}</button>
                                <a href="{{ route('admin.faq') }}" class="btn btn-light">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
