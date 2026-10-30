<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use FileManagerTrait;

    public function category()
    {
        return view('admin.pages.categories');
    }

    public function allCategories()
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();
        $formattedCategories = [];

        foreach ($categories as $category) {
            $formattedCategories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'url' => $category->slug ? route('collections.show', $category) : null,
                'cover_image' => $category->cover_image,
                'is_active' => $category->is_active,
            ];
        }

        $data['categories'] = $formattedCategories;

        return response()->json($data);
    }

    public function addCategory(Request $request)
    {
        $categories = Category::all();
        $data['categories'] = $categories;

        if ($request->id) {
            $category = Category::findOrFail($request->id);
            $data['category'] = $category;
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:120',
                'slug' => 'nullable|string|max:120|alpha_dash',
                'description' => 'nullable|string|max:2000',
                'sort_order' => 'nullable|integer|min:0',
                'parent_id' => 'nullable|exists:categories,id',
                'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'banner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            ], [
                'name.required' => 'Collection name is required',
                'slug.alpha_dash' => 'The URL handle can only use letters, numbers and dashes.',
            ]);

            $category = $request->id ? Category::findOrFail($request->id) : new Category();
            $before = basename((string) $category->getRawOriginal('cover_image'));
            $beforeBanner = basename((string) $category->getRawOriginal('banner_image'));

            $category->name = $request->name;
            $category->description = $request->description;
            $category->sort_order = (int) $request->input('sort_order', 0);
            $category->parent_id = $request->parent_id && (int) $request->parent_id !== (int) $category->id ? $request->parent_id : null;
            if ($request->filled('slug')) {
                $category->slug = $request->slug;
            }

            if ($request->hasFile('cover_image')) {
                $category->cover_image = $this->upload('upload/categories/', $request->file('cover_image'), $before ?: null);
            }
            if ($request->hasFile('banner_image')) {
                $category->banner_image = $this->upload('upload/categories/banners/', $request->file('banner_image'), $beforeBanner ?: null);
            }

            $category->save();

            return redirect()->route('admin.category')->with('success', $request->id ? 'Collection Updated Successfully' : 'Collection Added Successfully');
        }

        return view('admin.pages.addCategory', compact('data'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_active' => 'required',
            'id' => 'required',
        ]);

        $category = Category::findOrFail($request->id);
        $category->is_active = $request->is_active;
        $category->save();

        return response()->json(['success' => true, 'message' => 'Status Updated']);
    }

    public function deleteCategory(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->delete();

        return response()->json(['success' => true, 'message' => 'Delete Category Successfully']);
    }
}
