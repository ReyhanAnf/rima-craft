<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::orderBy('sort_order')->get();
        return view('galleries.index', compact('galleries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120', // Max 5MB
            'label' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
        ]);

        $path = $request->file('image')->store('galleries', 'public');

        Gallery::create([
            'image_url' => $path,
            'label' => $request->label,
            'title' => $request->title,
            'sort_order' => Gallery::max('sort_order') + 1,
        ]);

        return redirect()->route('galleries.index')->with('success', 'Foto berhasil ditambahkan ke galeri.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        if (Storage::disk('public')->exists($gallery->image_url)) {
            Storage::disk('public')->delete($gallery->image_url);
        }
        $gallery->delete();

        return redirect()->route('galleries.index')->with('success', 'Foto berhasil dihapus.');
    }
}
