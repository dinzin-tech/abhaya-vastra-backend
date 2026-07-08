<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GcsSetting;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Exception;

class StorageSettingController extends Controller
{
    public function getSettings()
    {
        $settings = GcsSetting::first();
        if (!$settings) {
            $settings = GcsSetting::create([
                'storage_driver' => 'local'
            ]);
        }
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    public function updateSettings(Request $request, StorageService $storageService)
    {
        $request->validate([
            'storage_driver' => 'required|in:local,gcs',
            'gcs_bucket' => 'required_if:storage_driver,gcs',
            'gcs_project_id' => 'required_if:storage_driver,gcs',
            'gcs_key_file' => 'required_if:storage_driver,gcs'
        ]);

        $driver = $request->storage_driver;
        $bucket = $request->gcs_bucket;
        $projectId = $request->gcs_project_id;
        $keyFile = $request->gcs_key_file;

        if ($driver === 'gcs') {
            try {
                $storageService->testConnection($driver, $bucket, $projectId, $keyFile);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        }

        $settings = GcsSetting::first();
        if (!$settings) {
            $settings = new GcsSetting();
        }

        $settings->storage_driver = $driver;
        $settings->gcs_bucket = $bucket;
        $settings->gcs_project_id = $projectId;
        $settings->gcs_key_file = is_array($keyFile) ? json_encode($keyFile) : $keyFile;
        $settings->save();

        $storageService->resolveSettings();

        return response()->json([
            'success' => true,
            'message' => 'Storage settings updated successfully.',
            'settings' => $settings
        ]);
    }
}
