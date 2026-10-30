@include('admin.layouts.header', ['title' => isset($data['attribute']) ? 'Edit Option' : 'Add Option'])
@include('admin.layouts.menu')

@php($attribute = $data['attribute'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ $attribute ? 'Edit Option' : 'Add Option' }}</h4>
                            <p class="text-muted mb-0 small">e.g. "Size" with XS, S, M, L, XL — or "Color" with a swatch for each colour. Used to build product variants.</p>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.add-attribute') }}" method="POST">
                                @csrf
                                @if ($attribute)
                                    <input type="hidden" name="id" value="{{ $attribute->id }}">
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Option Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $attribute->name ?? '') }}" required>
                                </div>

                                <label class="form-label">Values <small class="text-muted fw-normal">— in display order (e.g. XS, S, M, L). Colours can have a swatch.</small></label>
                                @php($valueRows = old('values', ($attribute?->values ?? collect())->map(fn ($v) => ['id' => $v->id, 'value' => $v->value, 'swatch' => $v->swatch])->all() ?: [['value' => '']]))
                                <div id="valueRows">
                                    @foreach (array_values($valueRows) as $i => $value)
                                        <div class="row value-row align-items-center mb-2">
                                            <input type="hidden" name="values[{{ $i }}][id]" value="{{ $value['id'] ?? '' }}">
                                            <div class="col-md-6">
                                                <input type="text" name="values[{{ $i }}][value]" class="form-control" value="{{ $value['value'] ?? '' }}" required placeholder="e.g. Black or M">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="values[{{ $i }}][swatch]" class="form-control swatch-input" value="{{ $value['swatch'] ?? '' }}" placeholder="#1E2A44 (colours)" maxlength="7" style="border-left: 22px solid {{ $value['swatch'] ?? '#fff' }}">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-value">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" id="addValueRow" class="btn btn-outline-primary btn-sm mt-1 mb-3">+ Add Value</button>
                                <div id="deletedValueFields"></div>

                                <div>
                                    <button type="submit" class="btn btn-primary">{{ $attribute ? 'Update Option' : 'Save Option' }}</button>
                                    <a href="{{ route('admin.attributes') }}" class="btn btn-light">Cancel</a>
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

<script>
    let valueIndex = {{ count($valueRows) }};
    document.getElementById('addValueRow').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row value-row align-items-center mb-2';
        row.innerHTML = `
            <div class="col-md-6"><input type="text" name="values[${valueIndex}][value]" class="form-control" required placeholder="e.g. Black or M"></div>
            <div class="col-md-3"><input type="text" name="values[${valueIndex}][swatch]" class="form-control swatch-input" placeholder="#1E2A44 (colours)" maxlength="7" style="border-left: 22px solid #fff"></div>
            <div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-value">Remove</button></div>
        `;
        document.getElementById('valueRows').appendChild(row);
        valueIndex++;
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('swatch-input') && /^#([0-9a-f]{3}){1,2}$/i.test(e.target.value)) {
            e.target.style.borderLeftColor = e.target.value;
        }
    });

    document.addEventListener('click', function (e) {
        if (!e.target.classList.contains('remove-value')) return;
        const row = e.target.closest('.value-row');
        const hiddenId = row.querySelector('input[name$="[id]"]');
        if (hiddenId && hiddenId.value) {
            const field = document.createElement('input');
            field.type = 'hidden';
            field.name = 'deleted_ids[]';
            field.value = hiddenId.value;
            document.getElementById('deletedValueFields').appendChild(field);
        }
        row.remove();
    });
</script>
