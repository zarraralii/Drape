<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteInfo;
use App\Models\StorySection;
use App\Rules\ImageSpec;
use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;

class StorySectionController extends Controller
{
    use FileManagerTrait;

    public function sections()
    {
        return view('admin.pages.storySections');
    }

    public function allSections()
    {
        $sections = StorySection::orderBy('sort_order')->orderBy('id')->get();
        $formattedSections = [];

        foreach ($sections as $section) {
            $formattedSections[] = [
                'id' => $section->id,
                'title' => $section->title,
                'image' => $section->image,
                'sort_order' => $section->sort_order,
                'is_active' => $section->is_active,
            ];
        }

        $data['sections'] = $formattedSections;

        return response()->json($data);
    }

    public function addSection(Request $request)
    {
        $data = [];

        if ($request->id) {
            $data['section'] = StorySection::findOrFail($request->id);
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'title' => 'required|string|max:150',
                'description' => 'nullable|string',
                'cta_label' => 'nullable|string|max:50',
                'cta_link' => 'nullable|string|max:255|required_with:cta_label',
                'sort_order' => 'nullable|integer|min:0',
                'image' => [$request->id ? 'nullable' : 'required', 'image', 'max:' . config('media.story_section.max_kb'), new ImageSpec('story_section')],
            ], [
                'image.required' => 'Please upload the section image',
                'cta_link.required_with' => 'Please enter the Button Link for the button label',
            ]);

            $section = $request->id ? StorySection::findOrFail($request->id) : new StorySection();
            $beforeImage = basename((string) $section->getRawOriginal('image'));

            $section->fill($request->only('title', 'description', 'cta_label', 'cta_link'));
            $section->sort_order = $request->input('sort_order', 0);
            if ($request->hasFile('image')) {
                $section->image = $this->upload('upload/story/', $request->file('image'), $beforeImage ?: null);
            }
            $section->save();

            $message = $request->id ? 'Story Section Updated Successfully' : 'Story Section Added Successfully';

            return redirect()->route('admin.story-sections')->with('success', $message);
        }

        return view('admin.pages.addStorySection', compact('data'));
    }

    /**
     * The intro at the top of the Our Story page (eyebrow, headline,
     * description) and the button label at the bottom. Stored on the single
     * site_infos row — this page only moved the form here from Site Info.
     */
    public function intro(Request $request)
    {
        $siteInfo = SiteInfo::first();

        if ($request->isMethod('post')) {
            $request->validate([
                'story_eyebrow' => 'nullable|string|max:255',
                'story_headline' => 'nullable|string|max:255',
                'story_description' => 'nullable|string',
                'story_cta_label' => 'nullable|string|max:255',
                'story_cta_link' => 'nullable|string|max:255',
            ]);

            $siteInfo = $siteInfo ?: new SiteInfo();
            $siteInfo->fill($request->only('story_eyebrow', 'story_headline', 'story_description', 'story_cta_label', 'story_cta_link'));
            $siteInfo->save();

            return redirect()->route('admin.story-intro')->with('success', 'Our Story Intro Updated Successfully');
        }

        return view('admin.pages.storyIntro', compact('siteInfo'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_active' => 'required',
            'id' => 'required',
        ]);

        $section = StorySection::findOrFail($request->id);
        $section->is_active = $request->is_active;
        $section->save();

        return response()->json(['success' => true, 'message' => 'Status Updated']);
    }

    public function deleteSection(Request $request)
    {
        $section = StorySection::findOrFail($request->id);
        $this->delete('upload/story/', $section->getRawOriginal('image'));
        $section->delete();

        return response()->json(['success' => true, 'message' => 'Story Section Deleted Successfully']);
    }
}
