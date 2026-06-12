<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RewardSetting;

class RewardPointsSettingsController extends Controller
{
    /**
     * Display the reward settings page.
     */
    public function index()
    {
        $setting = RewardSetting::first();
        return view('admin.modules.reward-settings.index', compact('setting'));
    }

    /**
     * Store or update reward settings.
     */
    public function store(Request $request)
    {
        $request->validate([
            'min_order_value' => 'required|numeric|min:0',
            'reward_base_amount' => 'required|numeric|min:0.01',
            'reward_points' => 'required|integer|min:1',
            'points_value' => 'required|numeric|min:0.01',
            'status' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'min_order_value',
            'reward_base_amount',
            'reward_points',
            'points_value'
        ]);
        
        $data['status'] = $request->has('status') ? 1 : 0;

        // Check if settings exist
        $setting = RewardSetting::first();
        
        if ($setting) {
            $setting->update($data);
            $message = 'Reward settings updated successfully.';
        } else {
            RewardSetting::create($data);
            $message = 'Reward settings created successfully.';
        }

        return redirect()->route('reward-settings.index')
            ->with('success', $message);
    }
}
