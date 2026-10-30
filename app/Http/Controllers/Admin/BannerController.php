<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Rules\ImageSpec;
use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use FileManagerTrait;

    public function banners()
    {
        return view('admin.pages.banners');
    }

    public function allBanners()
    {
        $banners = Banner::orderBy('sort_order')->orderByDesc('id')->get();
        $formattedBanners = [];

        foreach ($banners as $banner) {
            $formattedBanners[] = [
                'id' => $banner->id,
                'headline' => $banner->headline,
                'image' => $banner->image,
                'sort_order' => $banner->sort_order,
                'is_active' => $banner->is_active,
            ];
        }

        $data['banners'] = $formattedBanners;

        return response()->json($data);
    }

    public function addBanner(Request $request)
    {
        $data = [];

        if ($request->id) {
            $data['banner'] = Banner::findOrFail($request->id);
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'headline' => 'required|string|max:150',
                'eyebrow' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:255',
                'cta_label' => 'nullable|string|max:50',
                'cta_link' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
                'image' => 'nullable|image|max:5120',
                'background_image' => ['nullable', 'image', 'max:' . config('media.banner_background.max_kb'), new ImageSpec('banner_background')],
            ]);

            if ($request->id) {
                $banner = Banner::findOrFail($request->id);
                $beforeImage = basename((string) $banner->getRawOriginal('image'));
                $beforeBackground = basename((string) $banner->getRawOriginal('background_image'));
                $banner->fill($request->only('headline', 'eyebrow', 'description', 'cta_label', 'cta_link'));
                $banner->sort_order = $request->input('sort_order', 0);
                if ($request->hasFile('image')) {
                    $banner->image = $this->upload('upload/banners/', $request->file('image'), $beforeImage);
                }
                if ($request->hasFile('background_image')) {
                    $banner->background_image = $this->upload('upload/banners/', $request->file('background_image'), $beforeBackground);
                }
                $banner->save();

                $message = 'Banner Updated Successfully';
            } else {
                $banner = Banner::create([
                    ...$request->only('headline', 'eyebrow', 'description', 'cta_label', 'cta_link'),
                    'sort_order' => $request->input('sort_order', 0),
                ]);
                if ($request->hasFile('image')) {
                    $banner->image = $this->upload('upload/banners/', $request->file('image'));
                }
                if ($request->hasFile('background_image')) {
                    $banner->background_image = $this->upload('upload/banners/', $request->file('background_image'));
                }
                $banner->save();

                $message = 'Banner Added Successfully';
            }

            return redirect()->route('admin.banners')->with('success', $message);
        }

        return view('admin.pages.addBanner', compact('data'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_active' => 'required',
            'id' => 'required',
        ]);

        $banner = Banner::findOrFail($request->id);
        $banner->is_active = $request->is_active;
        $banner->save();

        return response()->json(['success' => true, 'message' => 'Status Updated']);
    }

    public function deleteBanner(Request $request)
    {
        $banner = Banner::findOrFail($request->id);
        $this->delete('upload/banners/', $banner->getRawOriginal('image'));
        $this->delete('upload/banners/', $banner->getRawOriginal('background_image'));
        $banner->delete();

        return response()->json(['success' => true, 'message' => 'Banner Deleted Successfully']);
    }
}
