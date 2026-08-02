<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(Request $request): Response
    {
        $announcements = Announcement::with('creator:id,name')
            ->latest()
            ->paginate(15)
            ->through(fn ($item) => [
                'id'         => $item->id,
                'title'      => $item->title,
                'content'    => $item->content,
                'type'       => $item->type,
                'is_active'  => $item->is_active,
                'version'    => $item->version,
                'url'        => $item->url,
                'created_by' => $item->creator?->name ?? 'System',
                'created_at' => $item->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'     => 'nullable|string|max:255',
            'content'   => 'required|string|max:1000',
            'type'      => 'required|string|in:info,warning,success,danger',
            'is_active' => 'boolean',
            'url'       => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = $request->user()?->id;

        Announcement::create($validated);

        return redirect()->back()->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title'     => 'nullable|string|max:255',
            'content'   => 'required|string|max:1000',
            'type'      => 'required|string|in:info,warning,success,danger',
            'is_active' => 'boolean',
            'url'       => 'nullable|string|max:500',
        ]);

        $announcement->update($validated);

        return redirect()->back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Toggle the active status of an announcement.
     */
    public function toggleActive(Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'is_active' => !$announcement->is_active,
        ]);

        $status = $announcement->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Pengumuman berhasil {$status}.");
    }

    /**
     * Force rebroadcast/show announcement again to all users by incrementing version.
     */
    public function rebroadcast(Announcement $announcement): RedirectResponse
    {
        $announcement->increment('version');

        return redirect()->back()->with('success', 'Pengumuman berhasil dimunculkan ulang untuk semua pengguna.');
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
