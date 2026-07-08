<?php

namespace App\Services;

use App\Models\GcsSetting;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class StorageService
{
    protected $driver;
    protected $bucketName;
    protected $projectId;
    protected $keyFileContent;
    protected $gcsClient;

    public function __construct()
    {
        $this->resolveSettings();
    }

    public function resolveSettings()
    {
        $settings = GcsSetting::first();
        if ($settings) {
            $this->driver = $settings->storage_driver;
            $this->bucketName = $settings->gcs_bucket;
            $this->projectId = $settings->gcs_project_id;
            $this->keyFileContent = $settings->gcs_key_file;
        } else {
            $this->driver = 'local';
        }

        if ($this->driver === 'gcs' && $this->keyFileContent) {
            try {
                $config = [
                    'projectId' => $this->projectId,
                ];
                $keyData = is_string($this->keyFileContent) ? json_decode($this->keyFileContent, true) : $this->keyFileContent;
                if (is_array($keyData)) {
                    $config['keyFile'] = $keyData;
                }
                $this->gcsClient = new StorageClient($config);
            } catch (Exception $e) {
                $this->driver = 'local';
            }
        }
    }

    public function upload($file, $folder = 'designs')
    {
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        if ($this->driver === 'gcs' && $this->gcsClient && $this->bucketName) {
            try {
                $bucket = $this->gcsClient->bucket($this->bucketName);
                $bucket->upload(
                    fopen($file->getRealPath(), 'r'),
                    [
                        'name' => $path,
                        'predefinedAcl' => 'publicRead'
                    ]
                );
                return $path;
            } catch (Exception $e) {
                // Fallback to local
            }
        }

        Storage::disk('public')->putFileAs($folder, $file, $filename);
        return $path;
    }

    public function getUrl($path)
    {
        if (empty($path)) return null;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if ($this->driver === 'gcs' && $this->bucketName) {
            return "https://storage.googleapis.com/{$this->bucketName}/{$path}";
        }

        return asset('storage/' . $path);
    }

    public function delete($path)
    {
        if (empty($path)) return;

        if ($this->driver === 'gcs' && $this->gcsClient && $this->bucketName) {
            try {
                $bucket = $this->gcsClient->bucket($this->bucketName);
                $object = $bucket->object($path);
                if ($object->exists()) {
                    $object->delete();
                }
                return;
            } catch (Exception $e) {}
        }

        Storage::disk('public')->delete($path);
    }

    public function testConnection($driver, $bucket, $projectId, $keyFile)
    {
        if ($driver === 'local') {
            return true;
        }

        try {
            $keyData = is_string($keyFile) ? json_decode($keyFile, true) : $keyFile;
            if (!is_array($keyData)) {
                throw new Exception("Invalid service account JSON key file.");
            }

            $client = new StorageClient([
                'projectId' => $projectId,
                'keyFile' => $keyData
            ]);

            $bucketObj = $client->bucket($bucket);
            if (!$bucketObj->exists()) {
                throw new Exception("Bucket '{$bucket}' does not exist or service account doesn't have access.");
            }

            $tempFileName = 'test_connection_' . Str::random(10) . '.txt';
            $object = $bucketObj->upload('connection test', [
                'name' => $tempFileName,
                'predefinedAcl' => 'publicRead'
            ]);
            $object->delete();

            return true;
        } catch (Exception $e) {
            throw new Exception("GCS Connection failed: " . $e->getMessage());
        }
    }
}
