<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function storeEditorImage(Request $request)
    {
        $request->validate([
            'image' => ['required','image','mimes:jpeg,jpg,png,gif,webp,svg','max:4096']
        ]);

        $file = $request->file('image');
        $ext = strtolower($file->getClientOriginalExtension());
        $name = Str::uuid()->toString().'.'.$ext;

        // Stocker directement sous public/editor/images
        $targetDir = public_path('editor/images');
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        $file->move($targetDir, $name);

        // URL publique sans /storage
        $publicUrl = asset('editor/images/'.$name);

        return response()->json([
            'url' => $publicUrl,
        ]);
    }
}