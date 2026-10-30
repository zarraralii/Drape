@include('admin.layouts.header', ['title' => 'Dashboard'])
@include('admin.layouts.menu')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            {{-- Product stats --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-h-100">
                        <a href="{{ route('admin.products') }}">
                            <div class="card-body">
                                <span class="text-muted mb-3 d-block">
                                    <i class="fa fa-box-open text-primary me-2"></i>Total Products
                                </span>
                                <h4>{{ $data['total_products'] }}</h4>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-h-100">
                        <a href="{{ route('admin.products') }}">
                            <div class="card-body">
                                <span class="text-muted mb-3 d-block">
                                    <i class="fa fa-check-circle text-success me-2"></i>Active Products
                                </span>
                                <h4>{{ $data['active_products'] }}</h4>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-h-100">
                        <a href="{{ route('admin.products') }}">
                            <div class="card-body">
                                <span class="text-muted mb-3 d-block">
                                    <i class="fa fa-times-circle text-danger me-2"></i>Out of Stock
                                </span>
                                <h4>{{ $data['out_of_stock_products'] }}</h4>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-h-100">
                        <a href="{{ route('admin.coupons') }}">
                            <div class="card-body">
                                <span class="text-muted mb-3 d-block">
                                    <i class="fa fa-ticket-alt text-warning me-2"></i>Active Coupons
                                </span>
                                <h4>{{ $data['active_coupons'] }} <small class="text-muted">/ {{ $data['total_coupons'] }}</small></h4>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Agent / commission stats --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-h-100">
                        <a href="{{ route('admin.agents') }}">
                            <div class="card-body">
                                <span class="text-muted mb-3 d-block">
                                    <i class="fa fa-user-tie text-info me-2"></i>Total Agents
                                </span>
                                <h4>{{ $data['total_agents'] }}</h4>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-h-100">
                        <a href="{{ route('admin.agent-sales') }}">
                            <div class="card-body">
                                <span class="text-muted mb-3 d-block">
                                    <i class="fa fa-hand-holding-usd text-success me-2"></i>Commission Owed
                                </span>
                                <h4>{{ money($data['total_commission_owed']) }}</h4>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <span class="text-muted mb-3 d-block">
                                <i class="fa fa-plus-circle text-primary me-2"></i>Quick Actions
                            </span>
                            <a href="{{ route('admin.add-product') }}" class="btn btn-sm btn-primary me-2 mb-1">+ Product</a>
                            <a href="{{ route('admin.add-coupon') }}" class="btn btn-sm btn-outline-primary me-2 mb-1">+ Coupon</a>
                            <a href="{{ route('admin.add-agent-sale') }}" class="btn btn-sm btn-outline-primary mb-1">+ Sale</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent activity --}}
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Recently Added Products</h5>
                        </div>
                        <div class="card-body">
                            @forelse ($data['recent_products'] as $product)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <div class="text-muted small">{{ $product->category->name ?? '—' }}</div>
                                    </div>
                                    <span>{{ money($product->price) }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No products yet. <a href="{{ route('admin.add-product') }}">Add your first one</a>.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Recent Agent Sales</h5>
                        </div>
                        <div class="card-body">
                            @forelse ($data['recent_sales'] as $sale)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div>
                                        <strong>{{ $sale->agent->name ?? 'Unknown agent' }}</strong>
                                        <div class="text-muted small">{{ $sale->product_name }} × {{ $sale->quantity }}</div>
                                    </div>
                                    <span class="text-success">{{ money($sale->commission_amount) }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No sales logged yet. <a href="{{ route('admin.add-agent-sale') }}">Log one</a>.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
