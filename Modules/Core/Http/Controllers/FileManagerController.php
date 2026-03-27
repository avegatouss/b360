<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Support\CurrentInstance;

final class FileManagerController extends Controller
{
    /**
     * Allowed MIME types for upload.
     */
    private const ALLOWED_MIMES = [
        // Images
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv', 'text/plain',
    ];

    private const ALLOWED_EXTENSIONS = 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,csv,txt';

    /**
     * Browse files in instance storage.
     */
    public function index(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            abort(404, 'Instance not found.');
        }
        $basePath = $this->basePath($instance->id);
        $subPath = $request->input('path', '');
        $subPath = $this->sanitizePath($subPath);

        $currentPath = $subPath ? $basePath . '/' . $subPath : $basePath;

        // Ensure directory exists
        if (!Storage::disk('public')->exists($currentPath)) {
            Storage::disk('public')->makeDirectory($currentPath);
        }

        $directories = collect(Storage::disk('public')->directories($currentPath))
            ->map(fn ($dir) => [
                'name' => basename($dir),
                'path' => str_replace($basePath . '/', '', $dir),
                'type' => 'folder',
            ]);

        $files = collect(Storage::disk('public')->files($currentPath))
            ->map(fn ($file) => [
                'name' => basename($file),
                'path' => str_replace($basePath . '/', '', $file),
                'full_path' => $file,
                'size' => Storage::disk('public')->size($file),
                'modified' => Storage::disk('public')->lastModified($file),
                'type' => 'file',
                'extension' => pathinfo($file, PATHINFO_EXTENSION),
                'is_image' => in_array(
                    strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                    ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']
                ),
                'url' => Storage::disk('public')->url($file),
            ]);

        // Breadcrumb
        $breadcrumbs = [];
        if ($subPath) {
            $parts = explode('/', $subPath);
            $accumulated = '';
            foreach ($parts as $part) {
                $accumulated = $accumulated ? $accumulated . '/' . $part : $part;
                $breadcrumbs[] = ['name' => $part, 'path' => $accumulated];
            }
        }

        $viewMode = $request->input('view', session('file_manager_view', 'grid'));
        session(['file_manager_view' => $viewMode]);

        return view('core::admin.file-manager', compact(
            'instance', 'directories', 'files', 'subPath', 'breadcrumbs', 'viewMode'
        ));
    }

    /**
     * Upload files.
     */
    public function upload(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            abort(404, 'Instance not found.');
        }

        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:10240', 'mimes:' . self::ALLOWED_EXTENSIONS],
        ]);

        $basePath = $this->basePath($instance->id);
        $subPath = $this->sanitizePath($request->input('path', ''));
        $targetPath = $subPath ? $basePath . '/' . $subPath : $basePath;

        $uploaded = 0;
        foreach ($request->file('files') as $file) {
            $file->storeAs($targetPath, $file->getClientOriginalName(), 'public');
            $uploaded++;
        }

        return back()->with('status', "{$uploaded} fichier(s) uploade(s) avec succes.");
    }

    /**
     * Download a file.
     */
    public function download(string $slug, Request $request)
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            abort(404, 'Instance not found.');
        }
        $filePath = $this->sanitizePath($request->input('file', ''));
        $basePath = $this->basePath($instance->id);
        $fullPath = $basePath . '/' . $filePath;

        if (!Storage::disk('public')->exists($fullPath)) {
            abort(404, 'Fichier introuvable.');
        }

        return Storage::disk('public')->download($fullPath, basename($filePath));
    }

    /**
     * Delete a file.
     */
    public function destroy(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            abort(404, 'Instance not found.');
        }
        $filePath = $this->sanitizePath($request->input('file', ''));
        $basePath = $this->basePath($instance->id);
        $fullPath = $basePath . '/' . $filePath;

        if (Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->delete($fullPath);
            return back()->with('status', 'Fichier supprime.');
        }

        // Try as directory
        if (Storage::disk('public')->directoryExists($fullPath)) {
            Storage::disk('public')->deleteDirectory($fullPath);
            return back()->with('status', 'Dossier supprime.');
        }

        return back()->with('error', 'Fichier introuvable.');
    }

    /**
     * Create a subfolder.
     */
    public function createFolder(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            abort(404, 'Instance not found.');
        }

        $request->validate([
            'folder_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-\s]+$/'],
        ]);

        $basePath = $this->basePath($instance->id);
        $subPath = $this->sanitizePath($request->input('path', ''));
        $targetPath = $subPath ? $basePath . '/' . $subPath : $basePath;
        $newFolder = $targetPath . '/' . $request->input('folder_name');

        Storage::disk('public')->makeDirectory($newFolder);

        return back()->with('status', 'Dossier cree.');
    }

    private function basePath(int $instanceId): string
    {
        return 'instances/' . $instanceId;
    }

    private function sanitizePath(?string $path): string
    {
        $path = (string) ($path ?? '');
        // Remove any directory traversal attempts
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $path = trim($path, '/');
        return $path;
    }
}
