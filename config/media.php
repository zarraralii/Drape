<?php

/*
|--------------------------------------------------------------------------
| Upload image specs
|--------------------------------------------------------------------------
|
| Each spec matches the frame the storefront actually renders the image in
| (measured at 1920px desktop width, see theme-custom.css). Images are shown
| with object-fit: cover, so an upload in the same ratio fills the frame
| exactly with nothing cropped. Used by App\Rules\ImageSpec (server side)
| and admin/partials/image-spec-check (client side) — if a frame's size
| changes in theme-custom.css, change it here too.
|
*/

return [

    // Allowed deviation from the exact ratio (0.02 = 2%).
    'tolerance' => 0.02,

    // Product cover, hover and gallery images.
    //   Product card:        270 × 360  (aspect-ratio: 3 / 4)
    //   Product detail:      540 × 720  (aspect-ratio: 3 / 4, max-width 540px)
    //   Home hero fallback:  360 × 480  (aspect-ratio: 3 / 4)
    // Minimum is the largest of these, so it never gets upscaled.
    'product' => [
        'width' => 270,
        'height' => 360,
        'min_width' => 540,
        'min_height' => 720,
        'recommended' => '1080 × 1440 px',
        'max_kb' => 5120,
    ],

    // "Our Story" page section image: story-section__media frame
    // (aspect-ratio: 4 / 3, max-width 560px), measured at 1920px width.
    'story_section' => [
        'width' => 560,
        'height' => 420,
        'min_width' => 560,
        'min_height' => 420,
        'recommended' => '1120 × 840 px',
        'max_kb' => 5120,
    ],

    // Homepage hero slide background: 1920 × 340 full-width slide.
    'banner_background' => [
        'width' => 1920,
        'height' => 340,
        'min_width' => 1920,
        'min_height' => 340,
        'recommended' => '1920 × 340 px',
        'max_kb' => 5120,
    ],

];
