<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Rules\ImageSpec;
use App\Traits\FileManagerTrait;
use App\Utils\Helper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    use FileManagerTrait;

    public const MAX_GALLERY_IMAGES = 10;

    public function products(Request $request)
    {
        $data = $request->all();

        return view('admin.pages.allProduct')->with(['data' => $data]);
    }

    public function allProduct(Request $request)
    {
        $products = Product::with('variations')->when($request->filled('out_of_stock'), function ($query) {
            $query->where(function ($sq) {
                $sq->where('type', '!=', 'variable')->where('qty', '<=', 2);
            })->orWhere(function ($sq) {
                $sq->where('type', 'variable')->whereHas('variations', function ($qq) {
                    $qq->where('quantity', '<=', 2);
                });
            });
        })->when($request->filled('is_active'), function ($query) use ($request) {
            $query->where('is_active', $request->query('is_active'));
        })->latest()->get();

        $formattedProducts = [];

        foreach ($products as $product) {
            $formattedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'cover_image_thumbnail' => $product->cover_image_thumbnail ?? $product->cover_image ?? '-',
                'published_date' => $product->published_date ? Carbon::parse($product->published_date)->format('F d, Y') : '-',
                'status' => $product->status,
                'is_active' => $product->is_active,
                'price' => money($product->price),
                'is_on_sale' => $product->is_on_sale,
                'current_price' => money($product->current_price),
                'stock' => $product->qty === null ? '∞' : (int) $product->qty,
                'variants' => $product->isVariable() ? $product->variations->count() : 0,
            ];
        }

        $data['products'] = $formattedProducts;

        return response()->json($data);
    }

    public function addProduct(Request $request)
    {
        $data['categories'] = Category::orderBy('name')->get();
        $data['attributes'] = Attribute::with(['values' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])->get();

        if ($request->id) {
            $product = Product::with('variations')->findOrFail($request->id);
            $data['product'] = $product;
            $data['variations'] = $product->variations;
        }

        if ($request->isMethod('post')) {
            $maxKb = config('media.product.max_kb');
            $isVariable = $request->input('type') === 'variable';

            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'type' => 'required|in:simple,variable',
                'price' => 'required|numeric|min:0',
                'qty' => 'nullable|integer|min:0',
                'discounted_price' => 'nullable|numeric|min:0',
                'published_date' => 'required|date',
                'short_description' => 'nullable|string',
                'material_care' => 'nullable|string',
                'cover_image' => [$request->id ? 'nullable' : 'required', 'image', "max:$maxKb", new ImageSpec('product')],
                'hover_image' => ['nullable', 'image', "max:$maxKb", new ImageSpec('product')],
                'additional_images' => [
                    'nullable',
                    'array',
                    function ($attribute, $value, $fail) use ($request) {
                        $existing = $request->id ? ProductImage::where('product_id', $request->id)->count() : 0;
                        $removing = count((array) $request->input('deleted_images', []));
                        $total = $existing - $removing + count((array) $value);

                        if ($total > self::MAX_GALLERY_IMAGES) {
                            $fail('A product can have at most ' . self::MAX_GALLERY_IMAGES . " additional images (this would make $total). Remove some first.");
                        }
                    },
                ],
                'additional_images.*' => ['image', "max:$maxKb", new ImageSpec('product')],
                'deleted_images' => 'nullable|array',
                'variants' => [$isVariable ? 'required' : 'nullable', 'array'],
                'variants.*.id' => 'nullable|integer',
                'variants.*.options' => 'required|json',
                'variants.*.sku' => 'nullable|string|max:64',
                'variants.*.price' => 'nullable|numeric|min:0',
                'variants.*.quantity' => 'required|integer|min:0',
                'variants.*.image' => 'nullable|image|max:' . $maxKb,
                'deleted_variants' => 'nullable|array',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
            ], [
                'name.required' => 'Name is required',
                'category_id.required' => 'Please select the Collection',
                'price.required' => 'Please enter the Price',
                'published_date.required' => 'Please select the Publish Date',
                'cover_image.required' => 'Please upload the cover image',
                'variants.required' => 'Add at least one size / colour variant, or switch the product to "Single item".',
                'variants.*.quantity.required' => 'Enter the stock for every variant (0 if none).',
            ]);

            try {
                DB::beginTransaction();

                $product = $request->id ? Product::with('gallery')->findOrFail($request->id) : new Product();
                $isNew = !$product->exists;

                $product->fill($request->only([
                    'name', 'type', 'price', 'discounted_price',
                    'discount_start_date', 'discount_end_date', 'short_description',
                    'description', 'material_care', 'meta_title', 'meta_description',
                ]));
                $product->is_discounted = $request->boolean('is_discounted');
                $product->qty = $isVariable ? 0 : ($request->filled('qty') ? (int) $request->qty : null);
                $product->slug = Helper::generateUniqueSlug($request->name, Product::class, 'slug', $product->id);
                $product->category_id = $request->category_id;
                $product->tags = $this->parseTags($request->tags);
                $product->published_date = Carbon::parse($request->published_date)->toDateString();
                $product->is_featured = $request->boolean('is_featured');
                $product->is_deal = $request->boolean('is_deal');

                if ($isNew) {
                    $product->status = 'Approved';
                    $product->is_active = 1;
                }

                if ($request->hasFile('cover_image')) {
                    $before = $isNew ? null : basename((string) $product->getRawOriginal('cover_image'));
                    $beforeThumbnail = $isNew ? null : basename((string) $product->getRawOriginal('cover_image_thumbnail'));
                    $file = $this->upload('upload/products/', $request->file('cover_image'), $before ?: null);
                    $product->cover_image = $file;
                    $product->cover_image_thumbnail = $this->generateThumbnail(
                        'upload/products/thumbnails',
                        time() . rand(111, 699) . '_thumbnail.jpg',
                        url('/') . '/upload/products/' . $file,
                        540, 720, 85, $beforeThumbnail ?: null,
                    );
                }

                if ($request->hasFile('hover_image')) {
                    $beforeHover = $isNew ? null : basename((string) $product->getRawOriginal('hover_image'));
                    $product->hover_image = $this->upload('upload/products/', $request->file('hover_image'), $beforeHover ?: null);
                }

                $product->save();

                if ($request->filled('deleted_images')) {
                    foreach (ProductImage::where('product_id', $product->id)->whereIn('id', $request->deleted_images)->get() as $image) {
                        $this->delete('upload/products/additional_images/', $image->getRawOriginal('image'));
                        $image->delete();
                    }
                }

                $this->storeAdditionalImages($product, $request->file('additional_images', []));

                if ($isVariable) {
                    $this->syncVariants($product, $request->input('variants', []), $request->file('variants', []), (array) $request->input('deleted_variants', []));
                }

                DB::commit();

                return redirect()->to(route('admin.products'))->with('success', $isNew ? 'Product Added Successfully' : 'Product Updated Successfully');
            } catch (ValidationException $e) {
                DB::rollBack();

                throw $e;
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);

                return redirect()->back()->withInput()->with('fail', 'Something went wrong while saving the product. Please try again.');
            }
        }

        return view('admin.pages.addProduct')->with(['data' => $data]);
    }

    /**
     * Save newly picked gallery images. Uses the uploaded files directly —
     * `$request->filled()` only looks at regular inputs, never files.
     */
    private function storeAdditionalImages(Product $product, array $files): void
    {
        $rows = [];

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $rows[] = [
                'product_id' => $product->id,
                'image' => $this->upload('upload/products/additional_images/', $file),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows) {
            ProductImage::insert($rows);
        }
    }

    /**
     * Each row is one size/colour combination from the variant builder:
     * options JSON like {"Color":"Black","Size":"M"}, stock, an optional
     * price (blank = the product's price) and an optional image shown
     * when that colour is picked.
     *
     * @throws ValidationException
     */
    private function syncVariants(Product $product, array $rows, array $files, array $deletedIds): void
    {
        $existing = $product->variations()->get()->keyBy('id');
        $seen = [];

        foreach ($deletedIds as $id) {
            if ($variant = $existing->get((int) $id)) {
                $this->delete('upload/variations/', $variant->getRawOriginal('image'));
                $variant->delete();
                $existing->forget((int) $id);
            }
        }

        foreach ($rows as $index => $row) {
            $options = collect(json_decode($row['options'] ?? '', true) ?: [])
                ->mapWithKeys(fn ($value, $name) => [trim((string) $name) => trim((string) $value)])
                ->filter(fn ($value, $name) => $name !== '' && $value !== '')
                ->all();

            if (!$options) {
                throw ValidationException::withMessages(["variants.$index.options" => 'Every variant needs at least one option (e.g. a size).']);
            }

            ksort($options);
            $signature = json_encode($options);
            if (isset($seen[$signature])) {
                throw ValidationException::withMessages(["variants.$index.options" => 'Two variants have the same options: ' . implode(' / ', $options) . '.']);
            }
            $seen[$signature] = true;

            $variant = !empty($row['id']) ? $existing->get((int) $row['id']) : null;
            $variant ??= new ProductVariation(['product_id' => $product->id]);

            $sku = trim((string) ($row['sku'] ?? '')) ?: $this->generateSku($product, $options);
            if (ProductVariation::where('sku', $sku)->when($variant->exists, fn ($q) => $q->where('id', '!=', $variant->id))->exists()) {
                throw ValidationException::withMessages(["variants.$index.sku" => "SKU \"$sku\" is already used by another variant."]);
            }

            $variant->fill([
                'attributes' => $options,
                'sku' => $sku,
                'quantity' => (int) $row['quantity'],
                'price' => isset($row['price']) && $row['price'] !== '' ? round((float) $row['price'], 2) : null,
            ]);

            if (!empty($files[$index]['image'])) {
                $before = $variant->exists ? basename((string) $variant->getRawOriginal('image')) : null;
                $variant->image = $this->upload('upload/variations/', $files[$index]['image'], $before ?: null);
            }

            $variant->save();
        }

        $product->syncStockFromVariants();
    }

    private function generateSku(Product $product, array $options): string
    {
        $base = strtoupper(Str::limit(Str::slug($product->name, ''), 6, '') . '-' . collect($options)->map(fn ($v) => Str::limit(Str::slug($v, ''), 4, ''))->implode('-'));

        do {
            $sku = $base . '-' . strtoupper(Str::random(3));
        } while (ProductVariation::where('sku', $sku)->exists());

        return $sku;
    }

    public function viewProduct(Request $request)
    {
        $data = null;
        if ($request->id) {
            $product = Product::with(['gallery', 'variations', 'category'])->findOrFail($request->id);
            $data['product'] = $product;
        }

        return view('admin.pages.viewProduct')->with(['data' => $data]);
    }

    /**
     * The tags field posts a comma-separated string (Choices.js text input);
     * store it as a clean, de-duplicated array.
     */
    private function parseTags($tags): array
    {
        $tags = is_array($tags) ? $tags : explode(',', (string) $tags);

        return array_values(array_unique(array_filter(array_map('trim', $tags), 'strlen')));
    }

    public function updateStatus(Request $request)
    {
        $request->validate(['status' => 'required', 'id' => 'required']);

        $product = Product::findOrFail($request->id);
        $product->status = $request->status;
        $product->save();

        return response()->json(['success' => true, 'message' => 'Status Updated']);
    }

    public function updateApprove(Request $request)
    {
        $request->validate(['is_active' => 'required|boolean', 'id' => 'required']);

        $product = Product::findOrFail($request->id);
        $product->is_active = $request->boolean('is_active');
        $product->save();

        return response()->json(['success' => true, 'message' => 'Product Status Updated']);
    }

    public function deleteProduct(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->delete();

        return response()->json(['success' => true, 'message' => 'Delete Product Successfully']);
    }

    /**
     * Option types such as Size or Colour. Each value has an order (used
     * for size runs and filters) and, for colours, an optional swatch hex.
     */
    public function addAttribute(Request $request)
    {
        $data = null;

        if ($request->id) {
            $data['attribute'] = Attribute::with(['values' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])->findOrFail($request->id);
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:40|regex:/^[\pL\pN _-]+$/u',
                'values' => 'required|array|min:1',
                'values.*.id' => 'nullable|integer',
                'values.*.value' => 'required|string|max:60',
                'values.*.swatch' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
                'deleted_ids' => 'nullable|array',
            ], [
                'name.regex' => 'Use letters, numbers and spaces only (e.g. "Size", "Color").',
                'values.*.swatch.regex' => 'Swatches must be a hex colour like #1E2A44.',
            ]);

            DB::transaction(function () use ($request) {
                $attribute = $request->id ? Attribute::findOrFail($request->id) : new Attribute();
                $attribute->name = trim($request->name);
                $attribute->save();

                if ($request->filled('deleted_ids')) {
                    AttributeValue::where('attribute_id', $attribute->id)->whereIn('id', $request->deleted_ids)->delete();
                }

                foreach (array_values($request->values) as $position => $row) {
                    $value = !empty($row['id'])
                        ? AttributeValue::where('attribute_id', $attribute->id)->find($row['id'])
                        : null;
                    $value ??= new AttributeValue(['attribute_id' => $attribute->id]);

                    $value->fill([
                        'value' => trim($row['value']),
                        'swatch' => $row['swatch'] ?? null,
                        'sort_order' => $position,
                    ]);
                    $value->attribute_id = $attribute->id;
                    $value->save();
                }
            });

            return redirect()->route('admin.attributes')->with('success', 'Option and values saved successfully!');
        }

        return view('admin.pages.addAttribute', ['data' => $data]);
    }

    public function allAttributes()
    {
        $data['attributes'] = Attribute::with(['values' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])->latest()->get();

        return response()->json($data);
    }

    public function attributes(Request $request)
    {
        $data = $request->all();

        return view('admin.pages.allAttribute')->with(['data' => $data]);
    }

    public function deleteAttribute(Request $request)
    {
        Attribute::findOrFail($request->id)->delete();

        return response()->json(['success' => true, 'message' => 'Delete Attribute Successfully']);
    }
}
