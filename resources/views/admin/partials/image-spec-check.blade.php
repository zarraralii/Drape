{{-- Client-side mirror of App\Rules\ImageSpec: checks ratio + minimum size as
soon as a file is picked, so the admin doesn't have to submit to find out.
Usage: <input type="file" data-image-spec="product" data-preview="#imgId"> --}}
<script>
    (function () {
        const specs = @json(collect(config('media'))->except('tolerance'));
        const tolerance = {{ config('media.tolerance') }};

        function check(file, spec) {
            return new Promise((resolve) => {
                const url = URL.createObjectURL(file);
                const img = new Image();
                img.onload = () => {
                    const w = img.naturalWidth, h = img.naturalHeight;
                    URL.revokeObjectURL(url);
                    const rw = spec.width, rh = spec.height;
                    const expected = rw / rh;
                    if (Math.abs(w / h - expected) / expected > tolerance) {
                        return resolve(`"${file.name}" is ${w} × ${h} px. It must have the same shape as the ${rw} × ${rh} px frame on the website (recommended ${spec.recommended}).`);
                    }
                    if (w < spec.min_width || h < spec.min_height) {
                        return resolve(`"${file.name}" is ${w} × ${h} px. It must be at least ${spec.min_width} × ${spec.min_height} px (recommended ${spec.recommended}).`);
                    }
                    resolve(null);
                };
                img.onerror = () => { URL.revokeObjectURL(url); resolve(`"${file.name}" is not a valid image.`); };
                img.src = url;
            });
        }

        document.querySelectorAll('input[type=file][data-image-spec]').forEach((input) => {
            const spec = specs[input.dataset.imageSpec];
            input.addEventListener('change', async function () {
                const files = Array.from(this.files || []);
                const errors = (await Promise.all(files.map((f) => check(f, spec)))).filter(Boolean);
                if (!errors.length) return;

                this.value = '';
                this.dispatchEvent(new Event('image-spec-cleared'));
                const preview = this.dataset.preview && document.querySelector(this.dataset.preview);
                if (preview) {
                    preview.dataset.original ? (preview.src = preview.dataset.original) : (preview.removeAttribute('src'), preview.style.display = 'none');
                }
                Swal.fire({ icon: 'error', title: 'Wrong image size', html: errors.join('<br>') });
            });
        });
    })();
</script>
