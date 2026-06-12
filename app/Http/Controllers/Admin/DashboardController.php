<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Products;
use App\Models\Customized;
use App\Models\Category;

class DashboardController extends Controller
{
    /**
     * For showing dashboard data
     *
     * @return View responce blade view with data
     */
     public function index()
    {
       
        $totalOrders =     Order::count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $shippedOrders =   Order::where('status', 'shipped')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $activeUsers =     User::count();
        $availableProducts = Products::count();
        $customProducts =  Customized::count();
        $productCategories =  Category::count();

        $metrics = [
            'totalOrders'       => $totalOrders,
            'deliveredOrders'   => $deliveredOrders,
            'shippedOrders'     => $shippedOrders,
            'cancelledOrders'   => $cancelledOrders,
            'activeUsers'       => $activeUsers,
            'availableProducts' => $availableProducts,
            'customProducts'    => $customProducts,
            'productCategories' => $productCategories,
        ];
        return view('admin.modules.dashboard.index', compact('metrics'));
    }
}
