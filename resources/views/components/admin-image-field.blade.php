@props(['field', 'label', 'value' => null, 'previewId' => null, 'inputId' => null, 'maxHeight' => '120px', 'small' => false])

<div class="mb-3">
    <label class="form-label">{{ $label }}</label>
    <div class="admin-image-preview{{ $value ? '' : ' d-none' }}" data-image-field-wrap="{{ $field }}">
        <img src="{{ $value }}" @if ($previewId) id="{{ $previewId }}" @endif class="img-fluid rounded d-block bg-dark p-2" style="max-height: {{ $maxHeight }};">
        <button type="button" class="admin-image-remove" data-field="{{ $field }}" title="Remove image">&times;</button>
    </div>
    <input type="file" name="{{ $field }}" @if ($inputId) id="{{ $inputId }}" @endif accept="image/*" class="form-control {{ $small ? 'form-control-sm' : '' }} {{ $value ? 'mt-2' : '' }}">
</div>
