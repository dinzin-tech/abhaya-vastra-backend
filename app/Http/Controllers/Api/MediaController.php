<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Exception;

class MediaController extends Controller
{
    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function serve(Request $request)
    {
        $path = $request->get('path');
        if (empty($path)) {
            abort(404);
        }

        $path = preg_replace('/^\/?storage\//', '', $path);
        $url = $this->storageService->getUrl($path);

        if (filter_var($url, FILTER_VALIDATE_URL) && !str_contains($url, 'localhost') && !str_contains($url, '127.0.0.1')) {
            try {
                $response = Http::get($url);
                if ($response->successful()) {
                    return response($response->body(), 200)
                        ->header('Content-Type', $response->header('Content-Type') ?: 'image/png')
                        ->header('Access-Control-Allow-Origin', '*');
                }
            } catch (Exception $e) {
                // fallback to local check
            }
        }

        if (Storage::disk('public')->exists($path)) {
            $file = Storage::disk('public')->get($path);
            $type = Storage::disk('public')->mimeType($path);

            return response($file, 200)
                ->header('Content-Type', $type)
                ->header('Access-Control-Allow-Origin', '*');
        }

        abort(404);
    }
}
