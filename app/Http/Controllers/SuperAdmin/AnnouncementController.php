<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderBy('sort_order')->orderByDesc('created_at')->get();
        return view('superadmin.announcements', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string',
            'image_url'   => 'nullable|string|max:500',
            'link_url'    => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        Announcement::create([
            'title'       => $request->title,
            'description' => $request->description,
            'image_url'   => $request->image_url,
            'link_url'    => $request->link_url,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $request->sort_order ?? 0,
        ]);

        return back()->with('success', 'Anuncio creado.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string',
            'image_url'   => 'nullable|string|max:500',
            'link_url'    => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $announcement->update([
            'title'       => $request->title,
            'description' => $request->description,
            'image_url'   => $request->image_url,
            'link_url'    => $request->link_url,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $request->sort_order ?? 0,
        ]);

        return back()->with('success', 'Anuncio actualizado.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Anuncio eliminado.');
    }

    public function toggle(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);
        return back()->with('success', 'Estado actualizado.');
    }
}
