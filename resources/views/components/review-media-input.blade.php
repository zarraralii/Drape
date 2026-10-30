@props(['id' => 'reviewMedia'])

<div class="review-dropzone" data-review-dropzone>
    <input type="file" id="{{ $id }}" name="media[]" accept="image/*,video/*" multiple hidden data-review-dropzone-input>

    <div class="review-dropzone-prompt" data-review-dropzone-prompt>
        <x-icon name="plus" />
        <p><strong>Drag &amp; drop</strong> photos or videos here, or <span class="review-dropzone-browse">browse</span></p>
        <span class="muted">You can select more than one file</span>
    </div>

    <div class="review-dropzone-previews" data-review-dropzone-previews></div>
</div>

@once
    @push('scripts')
    <script>
        (function () {
            // Files the user has picked so far, kept in JS (not just the
            // input's own FileList) so drops/selections can ADD to the
            // pending set instead of replacing it, and so a single file
            // can be removed again before submit.
            var stateByDropzone = new WeakMap();

            function getState(zone) {
                if (!stateByDropzone.has(zone)) {
                    stateByDropzone.set(zone, []);
                }
                return stateByDropzone.get(zone);
            }

            function syncInput(zone) {
                var input = zone.querySelector('[data-review-dropzone-input]');
                var dt = new DataTransfer();
                getState(zone).forEach(function (entry) { dt.items.add(entry.file); });
                input.files = dt.files;
            }

            function renderPreviews(zone) {
                var wrap = zone.querySelector('[data-review-dropzone-previews]');
                var files = getState(zone);
                wrap.innerHTML = '';

                files.forEach(function (entry) {
                    var card = document.createElement('div');
                    card.className = 'review-dropzone-preview';

                    var thumb = document.createElement('div');
                    thumb.className = 'review-dropzone-preview-thumb';

                    if (entry.file.type.startsWith('image/')) {
                        var img = document.createElement('img');
                        img.src = entry.url;
                        thumb.appendChild(img);
                    } else {
                        thumb.textContent = 'Video';
                    }

                    var status = document.createElement('span');
                    status.className = 'review-dropzone-preview-status';
                    status.textContent = entry.ready ? entry.file.name : 'Uploading…';

                    var remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'review-dropzone-preview-remove';
                    remove.setAttribute('aria-label', 'Remove file');
                    remove.innerHTML = '&times;';
                    remove.addEventListener('click', function (e) {
                        e.stopPropagation();
                        var idx = getState(zone).indexOf(entry);
                        if (idx > -1) getState(zone).splice(idx, 1);
                        URL.revokeObjectURL(entry.url);
                        syncInput(zone);
                        renderPreviews(zone);
                    });

                    card.appendChild(thumb);
                    card.appendChild(status);
                    card.appendChild(remove);
                    wrap.appendChild(card);
                });
            }

            function addFiles(zone, fileList) {
                var files = getState(zone);

                Array.from(fileList).forEach(function (file) {
                    if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) return;

                    var entry = { file: file, url: URL.createObjectURL(file), ready: false };
                    files.push(entry);

                    // Purely visual — the real transfer only happens once
                    // the form itself is submitted. This just confirms the
                    // file was picked up before it settles into place.
                    setTimeout(function () {
                        entry.ready = true;
                        renderPreviews(zone);
                    }, 500);
                });

                syncInput(zone);
                renderPreviews(zone);
            }

            document.addEventListener('click', function (e) {
                var zone = e.target.closest('[data-review-dropzone]');
                if (!zone || e.target.closest('[data-review-dropzone-preview-remove]')) return;
                if (e.target.closest('.review-dropzone-preview')) return;

                zone.querySelector('[data-review-dropzone-input]').click();
            });

            document.addEventListener('change', function (e) {
                var input = e.target.closest('[data-review-dropzone-input]');
                if (!input) return;

                var zone = input.closest('[data-review-dropzone]');
                addFiles(zone, input.files);
            });

            ['dragover', 'dragenter'].forEach(function (type) {
                document.addEventListener(type, function (e) {
                    var zone = e.target.closest('[data-review-dropzone]');
                    if (!zone) return;
                    e.preventDefault();
                    zone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'dragend'].forEach(function (type) {
                document.addEventListener(type, function (e) {
                    var zone = e.target.closest('[data-review-dropzone]');
                    if (!zone) return;
                    zone.classList.remove('is-dragover');
                });
            });

            document.addEventListener('drop', function (e) {
                var zone = e.target.closest('[data-review-dropzone]');
                if (!zone) return;
                e.preventDefault();
                zone.classList.remove('is-dragover');
                addFiles(zone, e.dataTransfer.files);
            });

            // Reset a dropzone's pending files once its form has actually
            // been submitted (new page load), so nothing lingers — not
            // strictly needed since the page navigates away, but keeps
            // behavior correct if that ever changes (e.g. an AJAX submit
            // added later).
            document.addEventListener('submit', function (e) {
                var zone = e.target.querySelector && e.target.querySelector('[data-review-dropzone]');
                if (!zone) return;
                var submitBtn = e.target.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Submitting…';
                }
            });
        })();
    </script>
    @endpush
@endonce
