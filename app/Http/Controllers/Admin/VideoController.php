<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class VideoController extends Controller
{
    /**
     * Display the Video listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.video.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Video rows for AJAX listing.
     */
    public function listVideo(Request $request)
    {
        $query = Video::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows' => view('admin.modules.video.list_rows', ['items' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating Video.
     */
    public function create()
    {
        return view('admin.modules.video.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update a Video entry.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'video' => $request->id
                ? 'nullable|file|mimes:mp4,webm,ogg|max:51200' // 50MB max
                : 'required|file|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = [
            'title' => $request->title,
        ];

        // ✅ Handle video upload
        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('short_videos', 'public');
            $data['video'] = $path;
        }

        // ✅ Update or Create record
        if ($request->id) {
            $video = Video::findOrFail($request->id);

            // Delete old video if new uploaded
            if (isset($data['video']) && $video->video && Storage::disk('public')->exists($video->video)) {
                Storage::disk('public')->delete($video->video);
            }

            $video->update($data);
            $message = 'Short Video Updated Successfully!';
        } else {
            Video::create($data);
            $message = 'Short Video Added Successfully!';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect' => route('video.index'),
        ]);
    }

    /**
     * Show the form for editing Video.
     */
    public function edit($id)
    {
        $item = Video::findOrFail($id);

        return view('admin.modules.video.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete Video entry.
     */
    public function delete(Request $request)
    {
        $video = Video::findOrFail($request->id);

        // Delete file from storage
        if ($video->video && Storage::disk('public')->exists($video->video)) {
            Storage::disk('public')->delete($video->video);
        }

        $video->delete();

        return response()->json([
            'message' => 'Short Video Deleted Successfully!',
        ], 200);
    }
}
