<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm"></span>
                    <span class="logo-lg">
                        <span class="logo-txt">{{ \App\Support\Store::brand()['name'] }}</span>
                    </span>
                </a>

                <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm"></span>
                    <span class="logo-lg">
                        <span class="logo-txt">{{ \App\Support\Store::brand()['name'] }}</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>
        </div>

        <div class="d-flex align-items-center">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="topbar-store-link"><i class="mdi mdi-storefront-outline font-size-18"></i> <span class="d-none d-md-inline">View store</span></a>
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @if (auth('admin')->check())
                        <span class="d-none d-xl-inline-block ms-1 fw-medium">{{ auth('admin')->user()->name }}</span>
                    @endif
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <form action="{{ route('admin.clear-cache') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                            <i class="mdi mdi-cached font-size-16 align-middle me-1"></i> Clear Cache &amp; Update
                        </button>
                    </form>
                    <form action="{{ route('admin.sitemap.generate') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                            <i class="mdi mdi-sitemap font-size-16 align-middle me-1"></i> Generate Sitemap
                        </button>
                    </form>
                    <form action="{{ route('auth.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                            <i class="mdi mdi-logout font-size-16 align-middle me-1"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'current' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow"><i class="fas fa-box-open"></i><span>Products</span></a>
                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                        <li><a href="{{ route('admin.add-product') }}"><i class="fas fa-plus-circle"></i><span>Add Product</span></a></li>
                        <li><a href="{{ route('admin.products') }}"><i class="fas fa-th-list"></i><span>All Products</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow"><i class="fas fa-images"></i><span>Banners</span></a>
                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                        <li><a href="{{ route('admin.add-banner') }}"><i class="fas fa-plus-circle"></i><span>Add Banner</span></a></li>
                        <li><a href="{{ route('admin.banners') }}"><i class="fas fa-th-list"></i><span>All Banners</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow"><i class="fas fa-book-open"></i><span>Our Story</span></a>
                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                        <li><a href="{{ route('admin.story-intro') }}"><i class="fas fa-heading"></i><span>Page Intro</span></a></li>
                        <li><a href="{{ route('admin.add-story-section') }}"><i class="fas fa-plus-circle"></i><span>Add Section</span></a></li>
                        <li><a href="{{ route('admin.story-sections') }}"><i class="fas fa-th-list"></i><span>All Sections</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow"><i class="fas fa-layer-group"></i><span>Collections</span></a>
                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                        <li><a href="{{ route('admin.add-category') }}"><i class="fas fa-plus-circle"></i><span>Add Collection</span></a></li>
                        <li><a href="{{ route('admin.category') }}"><i class="fas fa-th-list"></i><span>All Collections</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow"><i class="fas fa-sliders-h"></i><span>Options (Size, Color)</span></a>
                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                        <li><a href="{{ route('admin.add-attribute') }}"><i class="fas fa-plus-circle"></i><span>Add Option</span></a></li>
                        <li><a href="{{ route('admin.attributes') }}"><i class="fas fa-th-list"></i><span>All Options</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow"><i class="fas fa-ticket-alt"></i><span>Coupons</span></a>
                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                        <li><a href="{{ route('admin.add-coupon') }}"><i class="fas fa-plus-circle"></i><span>Add Coupon</span></a></li>
                        <li><a href="{{ route('admin.coupons') }}"><i class="fas fa-th-list"></i><span>All Coupons</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow"><i class="fas fa-user-tie"></i><span>Agents</span></a>
                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                        <li><a href="{{ route('admin.add-agent') }}"><i class="fas fa-plus-circle"></i><span>Add Agent</span></a></li>
                        <li><a href="{{ route('admin.agents') }}"><i class="fas fa-th-list"></i><span>All Agents</span></a></li>
                        <li><a href="{{ route('admin.add-agent-sale') }}"><i class="fas fa-plus-circle"></i><span>Log Sale / Commission</span></a></li>
                        <li><a href="{{ route('admin.agent-sales') }}"><i class="fas fa-hand-holding-usd"></i><span>Sales &amp; Commissions</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('admin.orders') }}" class="{{ request()->routeIs('admin.orders') ? 'current' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Orders</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.reviews') }}" class="{{ request()->routeIs('admin.reviews') ? 'current' : '' }}">
                        <i class="fas fa-star"></i>
                        <span>Reviews</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'current' : '' }}">
                        <i class="fas fa-user-friends"></i>
                        <span>Users</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow"><i class="fas fa-question-circle"></i><span>FAQs</span></a>
                    <ul class="sub-menu mm-collapse" aria-expanded="true">
                        <li><a href="{{ route('admin.add-faq') }}"><i class="fas fa-plus-circle"></i><span>Add FAQ</span></a></li>
                        <li><a href="{{ route('admin.faq') }}"><i class="fas fa-th-list"></i><span>All FAQs</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('admin.contact') }}" class="{{ request()->routeIs('admin.contact') ? 'current' : '' }}">
                        <i class="fas fa-phone-alt"></i>
                        <span>Contact Messages</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.siteinfo') }}" class="{{ request()->routeIs('admin.siteinfo') ? 'current' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>Site Info</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->
