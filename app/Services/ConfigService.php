<?php

namespace App\Services;

use App\Models\Config;
use App\Models\Network;
use App\Models\Factoidset;
use App\Models\Nameset;
use App\Models\Trial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ConfigService
{
    /**
     * Get all configuration settings.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllConfigs(): Collection
    {
        return Config::all();
    }

    /**
     * Get a configuration value by key.
     *
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function getConfigValue(string $key): mixed
    {
        return Cache::remember('config.' . $key, 3600, function () use ($key) {
            $value = Config::where('key', $key)->value('value');
            
            if ($value === null) {
                throw new \Exception('Configuration key not found');
            }

            return $value;
        });
    }

    /**
     * Update a configuration setting.
     *
     * @param string $key
     * @param mixed $value
     * @return Config
     * @throws ValidationException
     */
    public function updateConfig(string $key, mixed $value): Config
    {
        $validator = Validator::make([
            'key' => $key,
            'value' => $value
        ], [
            'key' => ['required', 'string'],
            'value' => ['required']
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $config = Config::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget('config.' . $key);

        Log::info('Configuration updated', [
            'key' => $key,
            'value' => $value,
            'user_id' => auth()->id()
        ]);

        return $config;
    }

    /**
     * Process uploaded configuration file.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    public function processConfigUpload(UploadedFile $file): array
    {
        $errors = [];
        $configData = json_decode($file->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidationException(
                Validator::make([], []),
                'Invalid JSON format'
            );
        }

        foreach ($configData as $config) {
            try {
                $this->validateConfig($config);
                $this->storeConfigFile($config);
                $this->createConfigModel($config);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
                Log::error('Configuration processing failed', [
                    'type' => $config['type'] ?? 'unknown',
                    'name' => $config['name'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $errors;
    }

    /**
     * Validate configuration data.
     *
     * @param array $config
     * @throws ValidationException
     */
    private function validateConfig(array $config): void
    {
        $validator = Validator::make($config, [
            'type' => ['required', 'string', 'in:network,factoid,names,trial'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Store configuration file.
     *
     * @param array $config
     */
    private function storeConfigFile(array $config): void
    {
        $fileName = Str::slug($config['name']) . '.json';
        Storage::put('config-files/' . $fileName, json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Create configuration model.
     *
     * @param array $config
     */
    private function createConfigModel(array $config): void
    {
        match ($config['type']) {
            'network' => Network::addNetworkFromConfig($config),
            'factoid' => Factoidset::addFactoidsetFromConfig($config),
            'names' => Nameset::addNamesetFromConfig($config),
            'trial' => Trial::addTrialFromConfig($config),
            default => throw new \InvalidArgumentException('Invalid configuration type')
        };

        Log::info('Configuration model created', [
            'type' => $config['type'],
            'name' => $config['name'],
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Delete configuration.
     *
     * @param string $type
     * @param int $id
     * @throws \InvalidArgumentException
     */
    public function deleteConfig(string $type, int $id): void
    {
        $model = match ($type) {
            'factoidset' => Factoidset::findOrFail($id),
            'network' => Network::findOrFail($id),
            'nameset' => Nameset::findOrFail($id),
            default => throw new \InvalidArgumentException('Invalid configuration type')
        };

        $model->delete();

        Log::info('Configuration deleted', [
            'type' => $type,
            'id' => $id,
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Get configuration file content.
     *
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public function getConfigFileContent(string $name): string
    {
        $configPath = storage_path('app/config-files/' . $name . '.json');
        
        if (!file_exists($configPath)) {
            throw new \Exception('Configuration file not found');
        }

        return file_get_contents($configPath);
    }

    /**
     * Clear configuration cache.
     *
     * @param string|null $key
     * @return void
     */
    public function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget('config.' . $key);
        } else {
            Cache::tags(['config'])->flush();
        }
    }

    /**
     * Get configuration file list.
     *
     * @return array
     */
    public function getConfigFileList(): array
    {
        return Storage::files('config-files');
    }

    /**
     * Validate configuration file.
     *
     * @param string $content
     * @return bool
     * @throws ValidationException
     */
    public function validateConfigFile(string $content): bool
    {
        $config = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidationException(
                Validator::make([], []),
                'Invalid JSON format'
            );
        }

        $validator = Validator::make($config, [
            'type' => ['required', 'string', 'in:network,factoid,names,trial'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }
} 