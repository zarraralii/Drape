<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <?php echo date('Y'); ?> © {{ \App\Support\Store::brand()['name'] }}.
            </div>

        </div>
    </div>
</footer>
</div>
<!-- end main content-->

</div>
<!-- END layout-wrapper -->

<!-- Right Sidebar -->
<div class="right-bar">

    <div data-simplebar class="h-100">

        <div class="rightbar-title d-flex align-items-center bg-dark p-3">

            <h5 class="m-0 me-2 text-white">Theme Customizer</h5>

            <a href="javascript:void(0);" class="right-bar-toggle ms-auto">

                <i class="mdi mdi-close noti-icon"></i>

            </a>

        </div>

        <!-- Settings -->
        <hr class="m-0" />

        <div class="p-4">

            <h6 class="mb-3">Layout</h6>

            <div class="form-check form-check-inline">

                <input class="form-check-input" type="radio" name="layout" id="layout-vertical" value="vertical">

                <label class="form-check-label" for="layout-vertical">Vertical</label>

            </div>

            <div class="form-check form-check-inline">

                <input class="form-check-input" type="radio" name="layout" id="layout-horizontal" value="horizontal">

                <label class="form-check-label" for="layout-horizontal">Horizontal</label>

            </div>

            <h6 class="mt-4 mb-3 pt-2">Layout Mode</h6>

            <div class="form-check form-check-inline">

                <input class="form-check-input" type="radio" name="layout-mode" id="layout-mode-light" value="light">

                <label class="form-check-label" for="layout-mode-light">Light</label>

            </div>

            <div class="form-check form-check-inline">

                <input class="form-check-input" type="radio" name="layout-mode" id="layout-mode-dark" value="dark">

                <label class="form-check-label" for="layout-mode-dark">Dark</label>

            </div>

            <h6 class="mt-4 mb-3 pt-2">Layout Width</h6>

            <div class="form-check form-check-inline">

                <input class="form-check-input" type="radio" name="layout-width" id="layout-width-fuild"
                    value="fuild" onchange="document.body.setAttribute('data-layout-size', 'fluid')">

                <label class="form-check-label" for="layout-width-fuild">Fluid</label>

            </div>

            <div class="form-check form-check-inline">

                <input class="form-check-input" type="radio" name="layout-width" id="layout-width-boxed"
                    value="boxed" onchange="document.body.setAttribute('data-layout-size', 'boxed')">

                <label class="form-check-label" for="layout-width-boxed">Boxed</label>

            </div>

            <h6 class="mt-4 mb-3 pt-2">Layout Position</h6>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="layout-position" id="layout-position-fixed"
                    value="fixed" onchange="document.body.setAttribute('data-layout-scrollable', 'false')">
                <label class="form-check-label" for="layout-position-fixed">Fixed</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="layout-position" id="layout-position-scrollable"
                    value="scrollable" onchange="document.body.setAttribute('data-layout-scrollable', 'true')">
                <label class="form-check-label" for="layout-position-scrollable">Scrollable</label>
            </div>

            <h6 class="mt-4 mb-3 pt-2">Topbar Color</h6>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="topbar-color" id="topbar-color-light"
                    value="light" onchange="document.body.setAttribute('data-topbar', 'light')">
                <label class="form-check-label" for="topbar-color-light">Light</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="topbar-color" id="topbar-color-dark"
                    value="dark" onchange="document.body.setAttribute('data-topbar', 'dark')">
                <label class="form-check-label" for="topbar-color-dark">Dark</label>
            </div>

            <h6 class="mt-4 mb-3 pt-2 sidebar-setting">Sidebar Size</h6>

            <div class="form-check sidebar-setting">
                <input class="form-check-input" type="radio" name="sidebar-size" id="sidebar-size-default"
                    value="default" onchange="document.body.setAttribute('data-sidebar-size', 'lg')">
                <label class="form-check-label" for="sidebar-size-default">Default</label>
            </div>
            <div class="form-check sidebar-setting">
                <input class="form-check-input" type="radio" name="sidebar-size" id="sidebar-size-compact"
                    value="compact" onchange="document.body.setAttribute('data-sidebar-size', 'md')">
                <label class="form-check-label" for="sidebar-size-compact">Compact</label>
            </div>
            <div class="form-check sidebar-setting">
                <input class="form-check-input" type="radio" name="sidebar-size" id="sidebar-size-small"
                    value="small" onchange="document.body.setAttribute('data-sidebar-size', 'sm')">
                <label class="form-check-label" for="sidebar-size-small">Small (Icon View)</label>
            </div>

            <h6 class="mt-4 mb-3 pt-2 sidebar-setting">Sidebar Color</h6>

            <div class="form-check sidebar-setting">
                <input class="form-check-input" type="radio" name="sidebar-color" id="sidebar-color-light"
                    value="light" onchange="document.body.setAttribute('data-sidebar', 'light')">
                <label class="form-check-label" for="sidebar-color-light">Light</label>
            </div>
            <div class="form-check sidebar-setting">
                <input class="form-check-input" type="radio" name="sidebar-color" id="sidebar-color-dark"
                    value="dark" onchange="document.body.setAttribute('data-sidebar', 'dark')">
                <label class="form-check-label" for="sidebar-color-dark">Dark</label>
            </div>
            <div class="form-check sidebar-setting">
                <input class="form-check-input" type="radio" name="sidebar-color" id="sidebar-color-brand"
                    value="brand" onchange="document.body.setAttribute('data-sidebar', 'brand')">
                <label class="form-check-label" for="sidebar-color-brand">Brand</label>
            </div>

            <h6 class="mt-4 mb-3 pt-2">Direction</h6>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="layout-direction" id="layout-direction-ltr"
                    value="ltr">
                <label class="form-check-label" for="layout-direction-ltr">LTR</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="layout-direction" id="layout-direction-rtl"
                    value="rtl">
                <label class="form-check-label" for="layout-direction-rtl">RTL</label>
            </div>

        </div>

    </div> <!-- end slimscroll-menu-->
</div>
{{-- @include('admin.deleteModel') --}}

<!-- Right bar overlay-->

<div class="rightbar-overlay"></div>

<!-- /Right-bar -->

<!-- JAVASCRIPT -->





<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Every admin AJAX call (deletes, status changes…) carries the CSRF token.
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
</script>

<script src="{{ asset('assets/admin/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

<script src="{{ asset('assets/admin/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<!-- Responsive examples -->
<script src="{{ asset('assets/admin/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>

<script src="{{ asset('assets/admin/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ asset('assets/admin/libs/apexcharts/apexcharts.min.js') }}"></script>

<script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('assets/admin/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>
<script src="{{ asset('assets/admin/libs/metismenu/metisMenu.min.js') }}"></script>

<script src="{{ asset('assets/admin/libs/simplebar/simplebar.min.js') }}"></script>

<script src="{{ asset('assets/admin/libs/node-waves/waves.min.js') }}"></script>

<script src="{{ asset('assets/admin/libs/feather-icons/feather.min.js') }}"></script>
<!-- pace js -->
<script src="{{ asset('assets/admin/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
{{-- <script src="{{ asset('assets/admin/libs/pace-js/pace.min.js') }}"></script> --}}
<script src="{{ asset('assets/admin/libs/@simonwep/pickr/pickr.min.js') }}"></script>
<script src="{{ asset('assets/admin/libs/@simonwep/pickr/pickr.es5.min.js') }}"></script>
<script src="{{ asset('assets/admin/libs/flatpickr/flatpickr.min.js') }}"></script>

<!-- glightbox js -->
<script src="{{ asset('assets/admin/libs/glightbox/js/glightbox.min.js') }}"></script>
<!-- choices js -->
<script src="{{ asset('assets/admin/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<!-- App js -->
<script src="{{ asset('assets/admin/js/app.js') }}"></script>
<script>
    //Classic editors
    $(document).ready(function() {

        // var dataTable =
        // var productsData = {!! isset($data['products']) ? json_encode($data['products']) : '[]' !!};












        $('.discount input[type=checkbox]').change(function() {
            if ($(this).prop('checked')) {
                $('.discount_percent').show();
            } else {
                $('.discount_percent').hide();
            }
        });
        $('.discount input[type=checkbox]').trigger('change');

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#previewImage').attr('src', e.target.result).fadeIn('slow');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#imageInput").change(function() {
            readURL(this);
        });

        function readBannerURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    var img = new Image();
                    img.src = e.target.result;

                    img.onload = function() {
                        if (img.width === 1265 && img.height === 320) {
                            $('#previewBannerImage').attr('src', e.target.result).fadeIn('slow');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Image Size',
                                text: `Image must be exactly 1265x320 pixels. Selected image is ${img.width}x${img.height}.`,
                            });
                            input.value = ''; // clear the input
                            $('#previewBannerImage').fadeOut('slow');
                        }
                    };
                };

                reader.readAsDataURL(input.files[0]);
            }
        }



        $("#imageBannerInput").change(function() {
            readBannerURL(this);
        });
        
        function readBannerMedia(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileType = file.type;
        
                // Create a FileReader
                const reader = new FileReader();
        
                reader.onload = function(e) {
                    // If it's an image
                    if (fileType.startsWith("image/")) {
                        const img = new Image();
                        img.src = e.target.result;
        
                        img.onload = function() {
                            if (img.width === 1265 && img.height === 320) {
                                $("#previewBannerContainer").html(
                                    `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover">`
                                );
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Invalid Image Size",
                                    text: `Image must be exactly 1265x320 pixels. Selected image is ${img.width}x${img.height}.`,
                                });
                                input.value = "";
                                $("#previewBannerContainer").html(`<p class="text-center mt-4">Upload image or video</p>`);
                            }
                        };
                    }
        
                    // If it's a video
                    else if (fileType.startsWith("video/")) {
                        const video = document.createElement("video");
                        video.src = e.target.result;
        
                        video.onloadedmetadata = function() {
                            if (video.videoWidth === 1265 && video.videoHeight === 320) {
                                $("#previewBannerContainer").html(
                                    `<video src="${e.target.result}" class="w-100 h-100 object-fit-cover" autoplay muted loop></video>`
                                );
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Invalid Video Size",
                                    text: `Video must be exactly 1265x320 pixels. Selected video is ${video.videoWidth}x${video.videoHeight}.`,
                                });
                                input.value = "";
                                $("#previewBannerContainer").html(`<p class="text-center mt-4">Upload image or video</p>`);
                            }
                        };
                    }
        
                    // Invalid type
                    else {
                        Swal.fire({
                            icon: "error",
                            title: "Invalid File",
                            text: "Please upload only image or video files.",
                        });
                        input.value = "";
                    }
                };
        
                reader.readAsDataURL(file);
            }
        }
        
        $("#mediaBannerInput").change(function() {
            readBannerMedia(this);
        });


        function readProductURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    var img = new Image();
                    img.src = e.target.result;

                    img.onload = function() {
                        if (img.width === 2024 && img.height === 2194) {
                            $('#previewProductImage').attr('src', e.target.result).fadeIn('slow');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Image Size',
                                text: `Image must be exactly 2024x2194 pixels. Selected image is ${img.width}x${img.height}.`,
                            });
                            input.value = ''; // clear the input
                            $('#previewProductImage').fadeOut('slow');
                        }
                    };
                };

                reader.readAsDataURL(input.files[0]);
            }
        }



        $("#imageProductInput").change(function() {
            readProductURL(this);
        });
        $('.banner-input').on('change', function () {
            readBannersURL(this);
        });
        
        function readBannersURL(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var index = input.getAttribute('data-index');
                var previewImg = document.getElementById('previewBannerImage' + index);
                var previewVideo = document.getElementById('previewBannerVideo' + index);
                var inputName = input.getAttribute('name');
                var wrapper = previewImg ? previewImg.parentElement : (previewVideo ? previewVideo.parentElement : null);
        
                // remove old video if exists
                if (wrapper) {
                    var oldVideo = wrapper.querySelector('video');
                    if (oldVideo) oldVideo.remove();
                }
        
                // set required dimensions
                var requiredWidth = (inputName === "home_banner") ? 1264 : 1265;
                var requiredHeight = (inputName === "home_banner") ? 500 : 270;
        
                // detect if file is image
                if (file.type.startsWith('image/')) {
                    var img = new Image();
                    img.src = URL.createObjectURL(file);
        
                    img.onload = function () {
                        if (img.width !== requiredWidth || img.height !== requiredHeight) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Image Size',
                                text: `Image must be exactly ${requiredWidth}x${requiredHeight} pixels. Selected image is ${img.width}x${img.height}.`,
                            });
                            $(input).val('');
                            return;
                        }
        
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            if (previewImg) {
                                previewImg.src = e.target.result;
                                previewImg.style.display = "block";
                            }
                        };
                        reader.readAsDataURL(file);
        
                        URL.revokeObjectURL(img.src);
                    };
                }
        
                // detect if file is video
                else if (file.type.startsWith('video/')) {
                    var videoURL = URL.createObjectURL(file);
                    var videoEl = document.createElement('video');
                    videoEl.src = videoURL;
                    videoEl.className = "w-100 h-100 object-fit-cover";
                    videoEl.autoplay = true;
                    videoEl.loop = true;
                    videoEl.muted = true;
                    videoEl.playsInline = true;
        
                    videoEl.onloadedmetadata = function () {
                        if (videoEl.videoWidth !== requiredWidth || videoEl.videoHeight !== requiredHeight) {
                            Swal.fire({
                                icon: "error",
                                title: "Invalid Video Size",
                                text: `Video must be exactly ${requiredWidth}x${requiredHeight} pixels. Selected video is ${videoEl.videoWidth}x${videoEl.videoHeight}.`,
                            });
                            $(input).val('');
                            videoEl.remove();
                            return;
                        }
        
                        if (previewImg) previewImg.style.display = "none";
                        if (wrapper) wrapper.appendChild(videoEl);
                    };
                }
        
                // invalid type
                else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Please upload only images or videos.',
                    });
                    $(input).val('');
                }
            }
        }






        if (document.querySelector('#tags')) {
            var multipleCancelButton = new Choices('#tags', {
                removeItemButton: true,
                duplicateItemsAllowed: false,
                delimiter: ',',
                paste: true,
                editItems: true,
                addItemFilter: (value) => value.trim() !== '',
                placeholderValue: 'Type a tag and press enter',
            });
            // Enter adds the tag — don't let it submit the whole product form.
            multipleCancelButton.input.element.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        }




        ClassicEditor
            .create(document.querySelector('#description'))
            .then(function(editor) {
                editor.ui.view.editable.element.style.height = '200px';
            })
            .catch(function(error) {
                console.error(error);
            });
        ClassicEditor
            .create(document.querySelector('#answer'))
            .then(function(editor) {
                editor.ui.view.editable.element.style.height = '200px';
            })
            .catch(function(error) {
                console.error(error);
            });
        ClassicEditor
            .create(document.querySelector('#short_description'))
            .then(function(editor) {
                editor.ui.view.editable.element.style.height = '200px';
            })
            .catch(function(error) {
                console.error(error);
            });
        ClassicEditor
            .create(document.querySelector('#tnc'))
            .then(function(editor) {
                editor.ui.view.editable.element.style.height = '200px';
            })
            .catch(function(error) {
                console.error(error);
            });
        flatpickr('#published_date', {
            enableTime: false,
            minDate: "today",
            dateFormat: "Y-m-d"
        });
        flatpickr('#discount_end_date', {
            enableTime: false,
            minDate: "today",
            dateFormat: "Y-m-d"
        });
        flatpickr('#discount_start_date', {
            enableTime: false,
            minDate: "today",
            dateFormat: "Y-m-d"
        });

    })
</script>

<script>
    $(document).ready(function() {
        var $dropZone = $('#dropZone');
        var $fileInput = $('#dropZoneImage');
        var $previewImage = $('#dropZonePreview');
        var $dropZoneText = $('#dropZoneText');

        // Click on drop zone opens file selector
        $dropZone.on('click', function() {
            $fileInput.click();
        });

        // Show preview
        function previewFile(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $previewImage.attr('src', e.target.result).fadeIn('fast');
                $dropZoneText.hide();
            };
            reader.readAsDataURL(file);
        }

        // On manual file input
        $fileInput.on('change', function() {
            if (this.files && this.files[0]) {
                previewFile(this.files[0]);
            }
        });

        // Prevent browser's default drag behaviors
        $(document).on('dragover drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        // On dragging over the drop zone
        $dropZone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dropZone.addClass('dragover');
        });

        // On drag leave
        $dropZone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dropZone.removeClass('dragover');
        });

        // On file drop
        $dropZone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dropZone.removeClass('dragover');

            var dt = e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                var droppedFile = dt.files[0];
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(droppedFile);
                $fileInput[0].files = dataTransfer.files;

                previewFile(droppedFile);
            }
        });
    });
</script>


</body>

</html>
