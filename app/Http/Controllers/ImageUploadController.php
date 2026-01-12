<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('images/content/temp', 'public');

            // Track uploaded images in session for cleanup
            $uploadedImages = session()->get('temp_uploaded_images', []);
            $uploadedImages[] = $path;
            session()->put('temp_uploaded_images', $uploadedImages);

            return response()->json([
                'location' => asset('storage/' . $path)
            ]);
        }

        return response()->json([
            'error' => 'No image uploaded'
        ], 400);
    }

    public function cleanup(Request $request)
    {
        $uploadedImages = session()->get('temp_uploaded_images', []);
        
        foreach ($uploadedImages as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        session()->forget('temp_uploaded_images');

        return response()->json(['success' => true]);
    }

    public function clearTracking(Request $request)
    {
        // Clear tracking without deleting images (called when post is saved)
        session()->forget('temp_uploaded_images');
        return response()->json(['success' => true]);
    }
}

