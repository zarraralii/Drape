<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\SiteInfo;
use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;

class SiteInfoController extends Controller
{
    use FileManagerTrait;

    /**
     * Every single-image column on site_infos that the "Site Info" form
     * can upload — the only fields the delete-image endpoint is allowed
     * to null out, so it can't be pointed at an arbitrary column.
     */
    private const IMAGE_FIELDS = [
        'logo',
        'contact_banner', 'about_banner', 'tnc_banner', 'faq_banner',
        'checkout_banner', 'cart_banner', 'favorite_banner', 'shop_banner',
        'privacy_banner', 'return_banner', 'account_banner', 'home_banner',
        'promo_banner_left', 'promo_banner_full', 'promo_banner_right',
    ];

    public function siteInfo(Request $request)
    {
        $data['site_info'] = SiteInfo::first();
        $data['gallery'] = GalleryImage::all();

        if ($request->isMethod('post')) {
            $banners = [
                'contact_banner', 'about_banner', 'tnc_banner', 'faq_banner',
                'checkout_banner', 'cart_banner', 'favorite_banner', 'shop_banner',
                'privacy_banner', 'return_banner', 'account_banner', 'home_banner',
                'promo_banner_left', 'promo_banner_full', 'promo_banner_right',
            ];
            $imageRule = 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120';

            $request->validate([
                'brand_name' => 'required|string|max:255',
                'announcement_text' => 'nullable|string|max:255',
                'currency' => 'nullable|string|max:10',
                'delivery_charges' => 'nullable|numeric|min:0',
                'free_shipping_threshold' => 'nullable|numeric|min:0',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'google_site_verification' => 'nullable|string|max:255',
                'logo' => $imageRule,
            ] + array_fill_keys($banners, $imageRule));

            $siteInfo = $data['site_info'];
            $isNew = !$siteInfo;
            $siteInfo = $siteInfo ?: new SiteInfo();

            $before = basename((string) $siteInfo->getRawOriginal('logo'));
            $siteInfo->fill($request->only([
                'brand_name', 'tagline', 'announcement_text', 'hero_description', 'hero_eyebrow', 'hero_subheadline', 'hero_cta_label',
                'privacy_policy', 'tnc', 'about', 'email', 'phone', 'location',
                'lat', 'lng', 'service_timing', 'facebook_link', 'instagram_link',
                'tiktok_link', 'youtube_link', 'return_and_exchange', 'shipping_policy', 'size_guide',
                'free_shipping_quote', 'gallery_quote',
                'promo_banner_left_link', 'promo_banner_full_link', 'promo_banner_right_link',
                'promo_banner_left_label', 'promo_banner_full_label', 'promo_banner_right_label',
                'meta_title', 'meta_description', 'google_site_verification',
            ]));
            $siteInfo->delivery_charges = round((float) $request->input('delivery_charges', 0), 2);
            $siteInfo->free_shipping_threshold = $request->filled('free_shipping_threshold') ? round((float) $request->free_shipping_threshold, 2) : null;
            $siteInfo->currency = strtoupper(trim((string) $request->input('currency'))) ?: config('store.currency');

            if ($request->hasFile('logo')) {
                $siteInfo->logo = $this->upload('upload/site_infos/', $request->file('logo'), $before ?: null);
            }

            foreach ($banners as $banner) {
                if ($request->hasFile($banner)) {
                    $beforeBanner = basename((string) $siteInfo->getRawOriginal($banner));
                    $siteInfo->$banner = $this->upload('upload/site_infos/', $request->file($banner), $beforeBanner ?: null);
                }
            }

            $siteInfo->save();

            return redirect()->to(route('admin.siteinfo'))->with('success', $isNew ? 'Site Info Saved Successfully' : 'Site Info Updated Successfully');
        }

        return view('admin.pages.site-info')->with(['data' => $data]);
    }

    /**
     * Deletes one of the single-image Site Info fields (logo or any
     * section/promo banner) — clears the file from disk
     * and nulls the column, leaving everything else on the row untouched.
     */
    public function deleteSiteInfoImage(Request $request)
    {
        $request->validate([
            'field' => 'required|string|in:' . implode(',', self::IMAGE_FIELDS),
        ]);

        $siteInfo = SiteInfo::first();
        $field = $request->field;

        if (!$siteInfo || !$siteInfo->getRawOriginal($field)) {
            return response()->json(['success' => false, 'message' => 'There is no image to delete.'], 404);
        }

        $this->delete('upload/site_infos/', $siteInfo->getRawOriginal($field));
        $siteInfo->$field = null;
        $siteInfo->save();

        return response()->json(['success' => true]);
    }

    /**
     * Adds one gallery image or video with its own CTA link — one file per
     * request (not a batch upload) so each row keeps its own link, the
     * same one-image-one-record shape Banner already uses. Image vs video
     * is detected from the uploaded file itself (its MIME type), not a
     * separate field the admin has to set.
     */
    public function storeGalleryImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm|max:20480',
            'cta_link' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $type = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image';

        $galleryImage = GalleryImage::create([
            'image' => $this->upload('upload/galleries/', $file),
            'type' => $type,
            'cta_link' => $request->cta_link,
        ]);

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $galleryImage->id,
                'image' => $galleryImage->image,
                'type' => $galleryImage->type,
                'cta_link' => $galleryImage->cta_link,
            ],
        ]);
    }

    /**
     * Updates a single gallery image's CTA link in place (called via
     * fetch from the admin page, matching deleteGalleryImage's pattern
     * of touching only that one row).
     */
    public function updateGalleryImage(Request $request, GalleryImage $galleryImage)
    {
        $request->validate([
            'cta_link' => 'nullable|string|max:255',
        ]);

        $galleryImage->cta_link = $request->cta_link;
        $galleryImage->save();

        return response()->json(['success' => true]);
    }

    /**
     * Deletes a single gallery image — its own row and file only, so the
     * rest of the gallery is unaffected.
     */
    public function deleteGalleryImage(GalleryImage $galleryImage)
    {
        $this->delete('upload/galleries/', $galleryImage->getRawOriginal('image'));
        $galleryImage->delete();

        return response()->json(['success' => true]);
    }

    public function faq()
    {
        return view('admin.pages.faqs');
    }

    public function allFaqs()
    {
        $data['faqs'] = Faq::orderBy('sort')->get()->map(fn ($faq) => [
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => $faq->answer,
        ]);

        return response()->json($data);
    }

    public function addFaq(Request $request)
    {
        $data = null;

        if ($request->id) {
            $data['faq'] = Faq::findOrFail($request->id);
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'question' => 'required',
                'answer' => 'required',
            ]);

            if ($request->id) {
                $faq = Faq::findOrFail($request->id);
                $faq->fill($request->only('question', 'answer', 'sort'));
                $faq->save();
                $message = 'Faq Updated Successfully';
            } else {
                $faq = Faq::create($request->only('question', 'answer', 'sort'));
                $message = 'Faq Added Successfully';
            }

            return redirect()->route('admin.faq')->with('success', $message);
        }

        return view('admin.pages.addFaq', compact('data'));
    }

    public function deleteFaq(Request $request)
    {
        Faq::findOrFail($request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Delete Faq Successfully']);
    }

    public function contact()
    {
        $contacts = Contact::latest()->get();

        return view('admin.pages.allContact')->with(['contacts' => $contacts]);
    }

    public function deleteContact(Request $request)
    {
        Contact::findOrFail($request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Delete Contact Successfully']);
    }
}
