@include('admin.layouts.header', ['title' => 'Product Details'])
@include('admin.layouts.menu')

@php($product = $data['product'] ?? null)

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            @if (!$product)
                <div class="alert alert-warning">Product not found.</div>
            @else
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">{{ $product->name }}</h4>
                                <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <img src="{{ $product->cover_image }}" alt="{{ $product->name }}" class="img-fluid rounded border">
                                    </div>
                                    @if ($product->gallery->isNotEmpty())
                                        <div class="col-md-8">
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach ($product->gallery as $image)
                                                    <img src="{{ $image->image }}" alt="" class="rounded border" style="width:90px;height:90px;object-fit:cover;">
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <table class="table table-borderless mb-4">
                                    <tbody>
                                        <tr>
                                            <th style="width:200px;">Category</th>
                                            <td>{{ $product->category->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Type</th>
                                            <td>{{ ucfirst($product->type ?? 'simple') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Price</th>
                                            <td>
                                                {{ money($product->current_price) }}
                                                @if ($product->is_on_sale)
                                                    <span class="text-muted text-decoration-line-through ms-2">{{ money($product->price) }}</span>
                                                    <span class="badge bg-success ms-1">-{{ $product->discount_percent }}%</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Quantity in Stock</th>
                                            <td>{{ $product->qty }}</td>
                                        </tr>
                                        <tr>
                                            <th>Published Date</th>
                                            <td>{{ $product->published_date ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tags</th>
                                            <td>
                                                @forelse ($product->tags ?? [] as $tag)
                                                    <span class="badge bg-light text-dark border me-1">{{ $tag }}</span>
                                                @empty
                                                    —
                                                @endforelse
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                @if ($product->short_description)
                                    <h5>Short Description</h5>
                                    <p class="text-muted">{{ $product->short_description }}</p>
                                @endif

                                @if ($product->description)
                                    <h5>Description</h5>
                                    <div>{!! $product->description !!}</div>
                                @endif

                                @if ($product->variations->isNotEmpty())
                                    <h5 class="mt-4">Variations</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>SKU</th>
                                                    <th>Variant</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($product->variations as $variation)
                                                    <tr>
                                                        <td>{{ $variation->sku }}</td>
                                                        <td>{{ $variation->label }}</td>
                                                        <td>{{ $variation->quantity }}</td>
                                                        <td>{{ money($variation->unitPrice($product)) }}@if ($variation->price === null) <small class="text-muted">(product price)</small>@endif</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                            <div class="card-body d-flex flex-column gap-2">
                                <a href="{{ route('admin.add-product', ['id' => $product->id]) }}" class="btn btn-primary">
                                    <i class="mdi mdi-pencil me-1"></i> Edit Product
                                </a>
                                <a href="{{ route('admin.products') }}" class="btn btn-light">
                                    <i class="mdi mdi-arrow-left me-1"></i> Back to All Products
                                </a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Ratings</h5></div>
                            <div class="card-body">
                                <p class="mb-1">Average rating: <strong>{{ number_format($product->rating ?? 0, 1) }} / 5</strong></p>
                                <p class="mb-3 text-muted">{{ $product->review_counts ?? 0 }} visible review(s)</p>
                                <a href="{{ route('admin.reviews', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="mdi mdi-star-outline me-1"></i> Manage Reviews
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@include('admin.layouts.footer')
