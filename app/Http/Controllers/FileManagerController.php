<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Folder;
use App\Models\Share;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;
use Carbon\Carbon;

class FileManagerController extends Controller
{
    public function show($path)
    {
        $file = Media::where('file_name', basename($path))->firstOrFail();
        $fullPath = storage_path('app/public/uploads/' . $file->file_name);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    public function getFiles(Request $request)
    {
        $query = Media::query();

        if ($request->folder) {
            $query->where('folder_id', $request->folder);
        } else {
            $query->whereNull('folder_id');
        }

        if ($request->trash) {
            $query->onlyTrashed();
        }

        if ($request->shared) {
            $query->whereNotNull('share_token');
        }

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $files = $query->get()->map(function($file) {
            return [
                'id' => $file->id,
                'name' => $file->name,
                'file_name' => $file->file_name,
                'file_size' => $file->file_size,
                'file_icon' => $file->file_icon,
                'mime_type' => $file->mime_type,
                'folder_id' => $file->folder_id,
                'share_token' => $file->share_token,
                'share_expires_at' => $file->share_expires_at,
                'deleted_at' => $file->deleted_at,
                'is_shared' => $file->isShared(),
                'is_expired' => $file->isShareExpired(),
                'created_at' => $file->created_at->diffForHumans(),
            ];
        });

        return response()->json($files);
    }

    public function getFolders()
    {
        $folders = Folder::withCount(['files', 'children'])->get()->map(function($folder) {
            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'parent_id' => $folder->parent_id,
                'file_count' => $folder->files_count,
                'children_count' => $folder->children_count,
                'full_path' => $folder->full_path,
                'share_token' => $folder->share_token,
                'deleted_at' => $folder->deleted_at,
            ];
        });

        return response()->json($folders);
    }

    public function getStats()
    {
        $total = Media::count();
        $images = Media::where('mime_type', 'like', 'image%')->count();
        $videos = Media::where('mime_type', 'like', 'video%')->count();
        $documents = Media::whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ])->count();

        return response()->json([
            'total' => $total,
            'images' => $images,
            'videos' => $videos,
            'documents' => $documents,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:102400',
        ]);

        $folderId = $request->folder_id;

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $uniqueName = $filename . '_' . Str::random(6) . '.' . $extension;

            $path = $file->storeAs('uploads', $uniqueName, 'public');

            Media::create([
                'model_type' => 'App\Models\User',
                'model_id' => 1,
                'name' => $originalName,
                'file_name' => $uniqueName,
                'mime_type' => $file->getMimeType(),
                'disk' => 'public',
                'size' => $file->getSize(),
                'collection_name' => 'default',
                'manipulations' => [],
                'custom_properties' => [],
                'generated_conversions' => [],
                'responsive_images' => [],
                'folder_id' => $folderId,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Files uploaded successfully']);
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json(['success' => true, 'folder' => $folder]);
    }

    public function moveItem(Request $request)
    {
        $request->validate([
            'type' => 'required|in:file,folder',
            'id' => 'required|integer',
            'target_type' => 'required|in:folder',
            'target_id' => 'required|integer|exists:folders,id',
        ]);

        if ($request->type === 'file') {
            $item = Media::findOrFail($request->id);
            $item->folder_id = $request->target_id;
            $item->save();
        } else {
            $item = Folder::findOrFail($request->id);
            $item->parent_id = $request->target_id;
            $item->save();
        }

        return response()->json(['success' => true]);
    }

    public function deleteFile($id)
    {
        $file = Media::findOrFail($id);
        
        if ($file->deleted_at) {
            Storage::disk('public')->delete('uploads/' . $file->file_name);
            $file->forceDelete();
        } else {
            $file->delete();
        }

        return response()->json(['success' => true]);
    }

    public function restoreFile($id)
    {
        $file = Media::withTrashed()->findOrFail($id);
        $file->restore();

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        Media::whereIn('id', $request->ids)->delete();

        return response()->json(['success' => true]);
    }

    public function downloadFile($id)
    {
        $file = Media::findOrFail($id);
        $path = storage_path('app/public/uploads/' . $file->file_name);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($path, $file->name);
    }

    public function bulkDownload(Request $request)
    {
        $ids = explode(',', $request->ids);
        $files = Media::whereIn('id', $ids)->get();

        if ($files->count() === 0) {
            return response()->json(['error' => 'No files selected'], 400);
        }

        if ($files->count() === 1) {
            return $this->downloadFile($files->first()->id);
        }

        $zip = new ZipArchive();
        $zipName = 'files_' . Str::random(8) . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $filePath = storage_path('app/public/uploads/' . $file->file_name);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $file->name);
                }
            }
            $zip->close();

            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        }

        return response()->json(['error' => 'Failed to create zip'], 500);
    }

    public function shareFile(Request $request, $id)
    {
        $file = Media::findOrFail($id);

        $token = $file->generateShareToken();

        if ($request->password) {
            $file->share_password = bcrypt($request->password);
        }

        if ($request->expires_in) {
            $file->share_expires_at = Carbon::now()->addHours($request->expires_in);
        }

        $file->save();

        $shareUrl = route('share.view', $token);

        return response()->json([
            'success' => true,
            'share_url' => $shareUrl,
            'token' => $token,
        ]);
    }

    public function previewFile($id)
    {
        $file = Media::findOrFail($id);
        $path = storage_path('app/public/uploads/' . $file->file_name);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $mimeType = $file->mime_type;
        $content = null;

        if (str_contains($mimeType, 'text') || $mimeType === 'application/pdf') {
            if (str_contains($mimeType, 'pdf')) {
                return response()->file($path, ['Content-Type' => 'application/pdf']);
            }
            $content = file_get_contents($path);
        } elseif (str_contains($mimeType, 'image')) {
            return response()->file($path);
        } elseif (str_contains($mimeType, 'video')) {
            return response()->file($path);
        }

        return response()->json([
            'name' => $file->name,
            'mime_type' => $mimeType,
            'size' => $file->file_size,
            'content' => $content,
            'is_previewable' => true,
        ]);
    }

    public function downloadFolder($id)
    {
        $folder = Folder::findOrFail($id);
        $files = Media::where('folder_id', $id)->get();

        if ($files->count() === 0) {
            return response()->json(['error' => 'Folder is empty'], 400);
        }

        $zip = new ZipArchive();
        $zipName = $folder->name . '_' . Str::random(6) . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $filePath = storage_path('app/public/uploads/' . $file->file_name);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $file->name);
                }
            }
            $zip->close();

            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        }

        return response()->json(['error' => 'Failed to create zip'], 500);
    }

    public function viewShared($token)
    {
        $file = Media::where('share_token', $token)->firstOrFail();

        if ($file->isShareExpired()) {
            return view('share-expired');
        }

        $path = storage_path('app/public/uploads/' . $file->file_name);

        if (!file_exists($path)) {
            return view('share-expired');
        }

        return view('share-preview', compact('file'));
    }
}