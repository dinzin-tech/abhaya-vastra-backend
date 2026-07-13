<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\QikinkService;

class QikinkSettingsController extends Controller
{
    /**
     * Display the Qikink integration settings page.
     */
    public function index()
    {
        $settings = Setting::getAllSettings();
        
        // Defaults if not set
        $settings['qikink_client_id'] = $settings['qikink_client_id'] ?? '';
        $settings['qikink_client_secret'] = $settings['qikink_client_secret'] ?? '';
        $settings['qikink_sandbox_mode'] = $settings['qikink_sandbox_mode'] ?? '1';
        $settings['qikink_default_print_type_id'] = $settings['qikink_default_print_type_id'] ?? '1';
        $settings['qikink_default_shipping'] = $settings['qikink_default_shipping'] ?? '1';
        $settings['qikink_auto_push'] = $settings['qikink_auto_push'] ?? '1';

        return view('admin.modules.qikink.settings', compact('settings'));
    }

    /**
     * Update the Qikink integration settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'qikink_client_id'             => 'required|string|max:255',
            'qikink_client_secret'         => 'required|string|max:255',
            'qikink_sandbox_mode'          => 'required|in:0,1',
            'qikink_default_print_type_id' => 'required|integer',
            'qikink_default_shipping'      => 'required|in:0,1',
            'qikink_auto_push'             => 'required|in:0,1',
        ]);

        $settings = $request->only([
            'qikink_client_id',
            'qikink_client_secret',
            'qikink_sandbox_mode',
            'qikink_default_print_type_id',
            'qikink_default_shipping',
            'qikink_auto_push'
        ]);

        Setting::setMultiple($settings);

        // Optionally test connection if saved successfully
        $qikinkService = app(QikinkService::class);
        $testResult = $qikinkService->testConnection();

        if ($testResult['success']) {
            return redirect()->route('admin.qikink.settings')
                ->with('success', 'Qikink settings updated and connection tested successfully!');
        } else {
            return redirect()->route('admin.qikink.settings')
                ->with('success', 'Qikink settings saved, but connection test failed: ' . $testResult['message'])
                ->withInput();
        }
    }
}
