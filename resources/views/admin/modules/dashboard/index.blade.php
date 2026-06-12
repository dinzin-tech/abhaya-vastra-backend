@extends('admin.layouts.app')
@push('meta')
<title>Dashboard | {{ config('app.name') }}</title>
<meta content="Admin Dashboard" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@push('appendCss')
  <style>
              @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

        /* Custom CSS Variables for Theming */
        :root {
            /* Primary Metrics */
            --color-orders: #4f46e5;       /* Indigo */
            --color-users: #06b6d4;        /* Cyan */
            --color-products: #10b981;     /* Emerald */
            --color-categories: #f97316;   /* Orange */
            
            /* Order Status Metrics */
            --color-delivered: #4cd137;   /* Lime Green / Success */
            --color-shipped: #3b82f6;     /* Blue / Processing */
            --color-cancelled: #ef4444;   /* Red / Danger */
            --color-custom: #c084fc;      /* Purple / Custom Products */
        }

        /* General Styling */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #eef1f5; 
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .main-container {
            max-width: 100%
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Page Title */
        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 2rem;
            border-bottom: 3px solid #d1d5db;
            padding-bottom: 0.75rem;
        }

        /* Dashboard Grid Layout (Mobile First) - Optimized for 8 cards (2 rows of 4) */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        /* Responsive Grid Adjustments */
        @media (min-width: 640px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (min-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: repeat(4, 1fr); /* Two rows of four cards */
            }
        }

        /* Card Styling */
        .stat-card {
            /* Subtle background gradient for depth */
            background: linear-gradient(145deg, #ffffff, #f7f7f7); 
            /* Padding is now on the anchor tag for full clickability */
            padding: 0; 
            border-radius: 1rem; /* Slightly more rounded */
            /* Enhanced shadow and soft border */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02); 
            border-left: 6px solid transparent; /* Thicker left border */
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); /* Smoother animation */
            position: relative;
        }

        .stat-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05);
            transform: translateY(-5px); /* More noticeable lift effect */
        }

        /* Link Styling to make the anchor fill the card */
        .card-link {
            display: block;
            height: 100%;
            padding: 1.5rem; /* Re-add the card padding here */
            text-decoration: none; /* Remove default underline */
            color: inherit; /* Inherit text color from body */
            border-radius: 1rem;
        }

        /* Icon Wrapper (Circle Background) */
        .icon-circle-wrapper {
            padding: 0.6rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            opacity: 0.9;
        }
        
        /* Card Specific Colors and Left Border */
        .card-orders { border-left-color: var(--color-orders); }
        .icon-indigo { color: var(--color-orders); }
        .bg-orders { background-color: rgba(79, 70, 229, 0.1); }

        .card-users { border-left-color: var(--color-users); }
        .icon-pink { color: var(--color-users); } 
        .bg-users { background-color: rgba(6, 182, 212, 0.1); }

        .card-products { border-left-color: var(--color-products); }
        .icon-green { color: var(--color-products); }
        .bg-products { background-color: rgba(16, 185, 129, 0.1); }

        .card-categories { border-left-color: var(--color-categories); }
        .icon-amber { color: var(--color-categories); }
        .bg-categories { background-color: rgba(249, 115, 22, 0.1); }

        /* NEW CARD STYLES */
        .card-delivered { border-left-color: var(--color-delivered); }
        .icon-delivered { color: var(--color-delivered); }
        .bg-delivered { background-color: rgba(76, 209, 55, 0.1); }

        .card-shipped { border-left-color: var(--color-shipped); }
        .icon-shipped { color: var(--color-shipped); }
        .bg-shipped { background-color: rgba(59, 130, 246, 0.1); }

        .card-cancelled { border-left-color: var(--color-cancelled); }
        .icon-cancelled { color: var(--color-cancelled); }
        .bg-cancelled { background-color: rgba(239, 68, 68, 0.1); }
        
        .card-custom { border-left-color: var(--color-custom); }
        .icon-custom { color: var(--color-custom); }
        .bg-custom { background-color: rgba(192, 132, 252, 0.1); }


        /* Header and Icons */
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center; /* Align title and icon wrapper vertically */
        }

        .stat-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .stat-icon {
            width: 20px; /* Adjusted for smaller icon inside circle */
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin="round"
        }

        /* Metric Value */
        .stat-value {
            margin-top: 0.75rem;
            font-size: 2.5rem;
            font-weight: 800;
            color: #111827;
        }

        .note-text {
            margin-top: 3rem;
            color: #9ca3af;
            font-size: 0.875rem;
            font-style: italic;
            text-align: center;
        }

    </style>

@endpush
@section('content')

<div class="app__slide-wrapper">
    <div class="row g-20">
        
        <div class="col-xxl-12 col-xl-12 col-lg-12">
             <div class="main-container">
        <h1 class="page-title">Dashboard</h1>

        <!-- Dashboard Grid Container --><div class="dashboard-grid">

            <!-- Card 1: Total Orders (Indigo Theme) --><div class="stat-card card-orders">
                <a href="{{ route('orders.index') }}" class="card-link">
                    <div class="stat-header">
                        <h2 class="stat-title">Total Orders</h2>
                        <div class="icon-circle-wrapper bg-orders">
                            <!-- Shopping Cart Icon --><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stat-icon icon-indigo">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $metrics['totalOrders'] }}</div>
                </a>
            </div>

            <!-- Card 2: Delivered Orders (NEW - Success Green Theme) --><div class="stat-card card-delivered">
                <a href="{{ route('orders.index', ['status' => 'delivered']) }}" class="card-link">
                    <div class="stat-header">
                        <h2 class="stat-title">Delivered Orders</h2>
                        <div class="icon-circle-wrapper bg-delivered">
                            <!-- Check Circle Icon --><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stat-icon icon-delivered">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value"> {{ $metrics['deliveredOrders'] }}</div>
                </a>
            </div>
            
            <!-- Card 3: Shipped Orders (NEW - Blue Theme) --><div class="stat-card card-shipped">
                <a href="{{ route('orders.index', ['status' => 'shipped']) }}" class="card-link">
                    <div class="stat-header">
                        <h2 class="stat-title">Shipped Orders</h2>
                        <div class="icon-circle-wrapper bg-shipped">
                            <!-- Truck Icon --><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stat-icon icon-shipped">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M19 18h-4M5 18H3"/><path d="M17 18a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M7 18a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M14 6h7l-3 3H14"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value"> {{ $metrics['shippedOrders'] }}</div>
                </a>
            </div>

            <!-- Card 4: Cancelled Orders (NEW - Red Theme) --><div class="stat-card card-cancelled">
                <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" class="card-link">
                    <div class="stat-header">
                        <h2 class="stat-title">Cancelled Orders</h2>
                        <div class="icon-circle-wrapper bg-cancelled">
                            <!-- X Circle Icon --><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stat-icon icon-cancelled">
                                <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $metrics['cancelledOrders'] }}</div>
                </a>
            </div>
            
            <!-- Card 5: Active Users (Cyan/Teal Theme) --><div class="stat-card card-users">
                <a href="{{ route('users.index') }}" class="card-link">
                    <div class="stat-header">
                        <h2 class="stat-title">Active Users</h2>
                        <div class="icon-circle-wrapper bg-users">
                            <!-- Users Icon --><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stat-icon icon-pink">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $metrics['activeUsers'] }}</div>
                </a>
            </div>

            <!-- Card 6: Available Products (Emerald Theme) --><div class="stat-card card-products">
                <a href="{{ route('products.index') }}" class="card-link">
                    <div class="stat-header">
                        <h2 class="stat-title">Available Products</h2>
                        <div class="icon-circle-wrapper bg-products">
                            <!-- T-shirt Icon --><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stat-icon icon-green">
                                <path d="M20.5 10a2.5 2.5 0 0 1-2.5 2.5H6A2.5 2.5 0 0 1 3.5 10v-3a.5.5 0 0 1 1-1h15a.5.5 0 0 1 1 1v3z"/>
                                <path d="M12 4L8 8"/>
                                <path d="M12 4L16 8"/>
                                <path d="M22 17.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 4 17.5v-2.5h16v2.5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $metrics['availableProducts'] }}</div>
                </a>
            </div>

            <!-- Card 7: Customized Products (Purple Theme) --><div class="stat-card card-custom">
                <a href="{{ route('customized.index') }}" class="card-link">
                    <div class="stat-header">
                        <h2 class="stat-title">Customized Products</h2>
                        <div class="icon-circle-wrapper bg-custom">
                            <!-- Sparkle/Star Icon --><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stat-icon icon-custom">
                                <path d="M12 2v2"/><path d="M12 20v2"/><path d="M20 12h2"/><path d="M2 12h2"/><path d="m18 6-2 2"/><path d="m8 16-2 2"/><path d="m16 8 2-2"/><path d="m6 18 2-2"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $metrics['customProducts'] }}</div>
                </a>
            </div>

            <!-- Card 8: Product Categories (Orange Theme) --><div class="stat-card card-categories">
                <a href="{{ route('categories.index') }}" class="card-link">
                    <div class="stat-header">
                        <h2 class="stat-title">Product Categories</h2>
                        <div class="icon-circle-wrapper bg-categories">
                            <!-- Folders Icon --><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stat-icon icon-amber">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                <path d="M2.93 17H21"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">{{ $metrics['productCategories'] }}</div>
                </a>
            </div>

        </div>
     
    </div>
        

       </div>
        
    </div>
</div>
@stop