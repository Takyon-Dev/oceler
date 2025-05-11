<?php

namespace App\Http\Controllers;

use App\Services\ConfigService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ConfigController extends Controller
{
    /**
     * @var ConfigService
     */
    private ConfigService $configService;

    /**
     * Create a new controller instance.
     *
     * @param ConfigService $configService
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
        $this->middleware('auth');
        $this->middleware('throttle:60,1')->only(['update', 'uploadConfig']);
    }

    /**
     * Display the configuration page.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $configs = $this->configService->getAllConfigs();
        return view('config.index', compact('configs'));
    }

    /**
     * Update configuration settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $config = $this->configService->updateConfig(
                $request->input('key'),
                $request->input('value')
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuration updated successfully',
                'data' => $config
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Configuration update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update configuration'
            ], 500);
        }
    }

    /**
     * Get all configuration settings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(): JsonResponse
    {
        $configs = $this->configService->getAllConfigs();
        return response()->json($configs);
    }

    /**
     * Get a specific configuration setting.
     *
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(string $key): JsonResponse
    {
        try {
            $value = $this->configService->getConfigValue($key);
            return response()->json(['value' => $value]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Upload configuration file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadConfig(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'config_file' => ['required', 'file', 'max:1024', 'mimes:json']
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            /** @var UploadedFile $file */
            $file = $request->file('config_file');
            $errors = $this->configService->processConfigUpload($file);

            if (!empty($errors)) {
                return back()->withErrors(['config_upload' => $errors]);
            }

            return back()->with('success', 'Configuration uploaded successfully');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Configuration upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['config_upload' => 'Failed to upload configuration']);
        }
    }

    /**
     * Delete configuration.
     *
     * @param string $type
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteConfig(string $type, int $id)
    {
        try {
            $this->configService->deleteConfig($type, $id);
            return redirect('/admin/config-files')->with('success', 'Configuration deleted successfully');
        } catch (\Exception $e) {
            Log::error('Configuration deletion failed', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['config_delete' => 'Failed to delete configuration']);
        }
    }

    /**
     * View configuration file.
     *
     * @param string $name
     * @return \Illuminate\Http\Response
     */
    public function viewConfig(string $name)
    {
        try {
            $content = $this->configService->getConfigFileContent($name);
            return response($content, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'inline'
            ]);
        } catch (\Exception $e) {
            Log::error('Configuration view failed', [
                'name' => $name,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to view configuration'], 500);
        }
    }
}
