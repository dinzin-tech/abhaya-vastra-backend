<div class="app-sidebar" id="sidebar">
    <div class="main-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="header-logo">
            <img class="main-logo" src="{{ asset('assets/images/logo/logo1.png') }}" alt="logo">
            <img class="dark-logo" src="{{ asset('assets/images/logo/logo1.png') }}" alt="logo">
        </a>
    </div>
    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="sidebar-left" id="sidebar-left"></div>
            <ul class="main-menu mt-100">
                <li class="sidebar__menu-category"><span class="category-name">Main</span></li>

                <!-- Dashboard -->
                <li class="slide">
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar__menu-item {{ areActiveRoutes(['admin.dashboard']) }}">
                        <div class="side-menu__icon"><i class="icon-house"></i></div>
                        <span class="sidebar__menu-label">Dashboard</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('users.index') }}"
                        class="sidebar__menu-item {{ areActiveRoutes(['users.index']) }}">
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Users</span>
                    </a>
                </li>

                <!-- Payments Management -->
                @php
                $isPaymentActive = request()->is('admin/payments*');
                $paymentStatus = request()->get('status');
                @endphp

                <li class="slide has-sub {{ $isPaymentActive ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="sidebar__menu-item">
                        <i class="fa-regular fa-angle-down side-menu__angle"></i>
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Payments</span>
                    </a>

                    <ul class="sidebar-menu child1">
                        <li class="slide">
                            <a class="sidebar__menu-item {{ !$paymentStatus ? 'active' : '' }}"
                                href="{{ route('payments.index') }}">All Payments</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $paymentStatus == 'pending' ? 'active' : '' }}"
                                href="{{ route('payments.index', ['status' => 'pending']) }}">Pending Payments</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $paymentStatus == 'processing' ? 'active' : '' }}"
                                href="{{ route('payments.index', ['status' => 'processing']) }}">Processing Payments</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $paymentStatus == 'completed' ? 'active' : '' }}"
                                href="{{ route('payments.index', ['status' => 'completed']) }}">Completed Payments</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $paymentStatus == 'failed' ? 'active' : '' }}"
                                href="{{ route('payments.index', ['status' => 'failed']) }}">Failed Payments</a>
                        </li>
                    </ul>
                </li>


                <!-- Pages Dropdown -->
                <li
                    class="slide has-sub {{ areActiveRoutes(['banner.index', 'about-us.index', 'terms.index', 'privacy.index', 'faq.index', 'video.index', 'reviews.index', 'gallery.index'], 'open') }}">
                    <a href="javascript:void(0);" class="sidebar__menu-item">
                        <i class="fa-regular fa-angle-down side-menu__angle"></i>
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Pages</span>
                    </a>
                    <ul class="sidebar-menu child1">
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['banner.index']) }}"
                                href="{{ route('banner.index') }}">Banners</a></li>
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['about-us.index']) }}"
                                href="{{ route('about-us.index') }}">About Us</a></li>
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['terms.index']) }}"
                                href="{{ route('terms.index') }}">Terms & Conditions</a></li>
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['privacy.index']) }}"
                                href="{{ route('privacy.index') }}">Privacy Policy</a></li>
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['faq.index']) }}"
                                href="{{ route('faq.index') }}">Faq</a></li>

                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['video.index']) }}"
                        href="{{ route('video.index') }}">Short Video</a></li>

                            <!-- <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['reviews.index']) }}"
                        href="{{ route('reviews.index') }}">Review</a></li> -->

                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['reviews.index']) }}"
                            href="{{ route('reviews.index') }}">Review</a></li>

                            <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['gallery.index']) }}"
                            href="{{ route('gallery.index') }}">Gallery</a></li>

                    </ul>
                </li>

                <li
                    class="slide has-sub {{ areActiveRoutes(['categories.index', 'products.index', 'product-colors.index', 'product-variants.index', 'customized.index', 'admin.print-designs.index'], 'open') }}">
                    <a href="javascript:void(0);" class="sidebar__menu-item">
                        <i class="fa-regular fa-angle-down side-menu__angle"></i>
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Products</span>
                    </a>
                    <ul class="sidebar-menu child1">
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['categories.index']) }}"
                                href="{{ route('categories.index') }}">Category</a></li>
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['products.index']) }}"
                                href="{{ route('products.index') }}">Products</a></li>
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['product-colors.index']) }}"
                                href="{{ route('product-colors.index') }}">Product Colors</a></li>
                        <li class="slide"><a
                                class="sidebar__menu-item {{ areActiveRoutes(['product-variants.index']) }}"
                                href="{{ route('product-variants.index') }}">Products Verients</a></li>
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['customized.index']) }}"
                                href="{{ route('customized.index') }}">Customized Products</a></li>
                        <li class="slide"><a class="sidebar__menu-item {{ areActiveRoutes(['admin.print-designs.index']) }}"
                                href="{{ route('admin.print-designs.index') }}">Print Designs</a></li>
                    </ul>
                </li>

                <!-- Orders Management -->
                <!-- <li class="slide has-sub {{ areActiveRoutes(['orders.index'], 'open') }}">
                    <a href="javascript:void(0);" class="sidebar__menu-item">
                        <i class="fa-regular fa-angle-down side-menu__angle"></i>
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Orders</span>
                    </a>
                    <ul class="sidebar-menu child1">
                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['orders.index']) }}" href="{{ route('orders.index') }}">All Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item" href="{{ route('orders.index', ['status' => 'pending']) }}">Pending Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item" href="{{ route('orders.index', ['status' => 'processing']) }}">Processing Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item" href="{{ route('orders.index', ['status' => 'shipped']) }}">Shipped Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item" href="{{ route('orders.index', ['status' => 'delivered']) }}">Delivered Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item" href="{{ route('orders.index', ['status' => 'cancelled']) }}">Cancelled Orders</a>
                        </li>
                    </ul>
                </li> -->
                <!-- Orders Management -->

                @php
    $isReturnActive = request()->is('admin/returns*');
@endphp

<li class="slide has-sub {{ $isReturnActive ? 'open' : '' }}">
    <a href="javascript:void(0);" class="sidebar__menu-item">
        <i class="fa-regular fa-angle-down side-menu__angle"></i>
        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
        <span class="sidebar__menu-label">Returns</span>
    </a>

    <ul class="sidebar-menu child1">
        <li class="slide">
            <a class="sidebar__menu-item {{ !request()->has('status') ? 'active' : '' }}"
               href="{{ route('returns.index') }}">All Returns</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'pending' ? 'active' : '' }}"
               href="{{ route('returns.index', ['status' => 'pending']) }}">Pending Returns</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'approved' ? 'active' : '' }}"
               href="{{ route('returns.index', ['status' => 'approved']) }}">Approved Returns</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'rejected' ? 'active' : '' }}"
               href="{{ route('returns.index', ['status' => 'rejected']) }}">Rejected Returns</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'completed' ? 'active' : '' }}"
               href="{{ route('returns.index', ['status' => 'completed']) }}">Completed Returns</a>
        </li>
    </ul>
</li>

<!-- Exchanges Management -->
@php
    $isExchangeActive = request()->is('admin/exchanges*');
@endphp

<li class="slide has-sub {{ $isExchangeActive ? 'open' : '' }}">
    <a href="javascript:void(0);" class="sidebar__menu-item">
        <i class="fa-regular fa-angle-down side-menu__angle"></i>
        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
        <span class="sidebar__menu-label">Exchanges</span>
    </a>

    <ul class="sidebar-menu child1">
        <li class="slide">
            <a class="sidebar__menu-item {{ !request()->has('status') && request()->is('admin/exchanges*') ? 'active' : '' }}"
               href="{{ route('exchanges.index') }}">All Exchanges</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'pending' ? 'active' : '' }}"
               href="{{ route('exchanges.index', ['status' => 'pending']) }}">Pending Exchanges</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'approved' ? 'active' : '' }}"
               href="{{ route('exchanges.index', ['status' => 'approved']) }}">Approved Exchanges</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'rejected' ? 'active' : '' }}"
               href="{{ route('exchanges.index', ['status' => 'rejected']) }}">Rejected Exchanges</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'pickup_scheduled' ? 'active' : '' }}"
               href="{{ route('exchanges.index', ['status' => 'pickup_scheduled']) }}">Pickup Scheduled</a>
        </li>
        <li class="slide">
            <a class="sidebar__menu-item {{ request()->get('status') == 'completed' ? 'active' : '' }}"
               href="{{ route('exchanges.index', ['status' => 'completed']) }}">Completed Exchanges</a>
        </li>
    </ul>
</li>
                @php
                $isOrderActive = request()->is('admin/orders*');
                $orderStatus = request()->get('status');
                @endphp

                <li class="slide has-sub {{ $isOrderActive ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="sidebar__menu-item">
                        <i class="fa-regular fa-angle-down side-menu__angle"></i>
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Orders</span>
                    </a>

                    <ul class="sidebar-menu child1">
                        <li class="slide">
                            <a class="sidebar__menu-item {{ (!request()->has('status') && !request()->has('shiprocket')) ? 'active' : '' }}"
                                href="{{ route('orders.index') }}">All Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $orderStatus == 'pending' ? 'active' : '' }}"
                                href="{{ route('orders.index', ['status' => 'pending']) }}">Pending Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $orderStatus == 'processing' ? 'active' : '' }}"
                                href="{{ route('orders.index', ['status' => 'processing']) }}">Processing Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $orderStatus == 'shipped' ? 'active' : '' }}"
                                href="{{ route('orders.index', ['status' => 'shipped']) }}">Shipped Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $orderStatus == 'delivered' ? 'active' : '' }}"
                                href="{{ route('orders.index', ['status' => 'delivered']) }}">Delivered Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ $orderStatus == 'cancelled' ? 'active' : '' }}"
                                href="{{ route('orders.index', ['status' => 'cancelled']) }}">Cancelled Orders</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ request()->get('shiprocket') == '1' ? 'active' : '' }}"
                                href="{{ route('orders.index', ['shiprocket' => '1']) }}">Delivery Management</a>
                        </li>
                    </ul>
                </li>

                <!-- Coupons Code -->


                <li class="slide">
                    <a href="{{ route('coupons.index') }}"
                        class="sidebar__menu-item {{ areActiveRoutes(['coupons.index']) }}">
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Generate Coupon</span>
                    </a>
                </li>

                <!-- Reward Points Management -->
                <li class="slide has-sub {{ areActiveRoutes(['rewards.index', 'reward-settings.index', 'points.index'], 'open') }}">
                    <a href="javascript:void(0);" class="sidebar__menu-item">
                        <i class="fa-regular fa-angle-down side-menu__angle"></i>
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Reward Points</span>
                    </a>
                    <ul class="sidebar-menu child1">
                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['rewards.index']) }}"
                                href="{{ route('rewards.index') }}">Transactions</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['reward-settings.index']) }}"
                                href="{{ route('reward-settings.index') }}">Settings</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['points.index']) }}"
                                href="{{ route('points.index') }}">Points Config</a>
                        </li>
                    </ul>
                </li>



                <!-- Settings Dropdown -->
                <li
                    class="slide has-sub {{ areActiveRoutes(['settings.index', 'smtp.index', 'contact-us.index', 'social.index', 'payment-gateway.index', 'admin.storage.index'], 'open') }}">
                    <a href="javascript:void(0);" class="sidebar__menu-item">
                        <i class="fa-regular fa-angle-down side-menu__angle"></i>
                        <div class="side-menu__icon"><i class="icon-hrm"></i></div>
                        <span class="sidebar__menu-label">Settings</span>
                    </a>
                    <ul class="sidebar-menu child1">
                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['settings.index']) }}"
                                href="{{ route('settings.index') }}">General Settings</a>
                        </li>

                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['smtp.index']) }}"
                                href="{{ route('smtp.index') }}">SMTP</a>
                        </li>
                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['contact-us.index']) }}" href="{{ route('contact-us.index') }}">Contact Us</a>
                        </li>

                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['social.index']) }}"
                                href="{{ route('social.index') }}">Social Icons</a>
                        </li>

                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['payment-gateway.index']) }}"
                                href="{{ route('payment-gateway.index') }}">Payment Gateway</a>
                        </li>

                        <li class="slide">
                            <a class="sidebar__menu-item {{ areActiveRoutes(['admin.storage.index']) }}"
                                href="{{ route('admin.storage.index') }}">Storage Settings</a>
                        </li>
                    </ul>
                </li>

            </ul>

            <div class="sidebar-right" id="sidebar-right"></div>
        </nav>

        <div class="sidebar__thumb sidebar-bg" data-background="{{ asset('assets/images/bg/side-bar.png') }}"
            style="background-image: url('{{ asset('assets/images/bg/side-bar.png') }}');">
            <div class="sidebar__thumb-content">
                <a class="btn btn-white rounded-pill w-100" href="javascript:void(0);" onclick="logout();">Logout</a>
            </div>
        </div>
    </div>
</div>
<div class="app__offcanvas-overlay"></div>