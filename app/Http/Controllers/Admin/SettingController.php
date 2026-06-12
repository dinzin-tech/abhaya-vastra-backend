<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display the general settings form.
     */
    public function index()
    {
        // Get all settings via model helper (cached)
        $settings = Setting::getAllSettings();

        return view('admin.modules.settings.index', compact('settings'));
    }

    /**
     * Store or update settings.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'site_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'currency' => 'nullable|string|max:10',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png|max:1024',
            'min_order_value' => 'required|numeric|min:0',
        ]);

        // Collect the input fields
        $settings = $request->only([
            'site_name',
            'email',
            'currency',
            'short_description',
            'long_description',
            'min_order_value',
            'reward_base_amount',
            'reward_points_per_base',
        ]);

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            $settings['logo'] = $path;
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            $settings['favicon'] = $path;
        }

        // Use the model helper for bulk update
        Setting::setMultiple($settings);

        return back()->with('success', 'Settings saved successfully.');
    }
}
