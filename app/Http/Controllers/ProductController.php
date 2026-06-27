<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProductController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Product::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $products = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        $product = new Product();
        $product->name = $validated['name'];
        $product->description = $validated['description'] ?? '';
        $product->base_price = $validated['base_price'];
        $product->current_stock = $validated['current_stock'];

        if ($request->hasFile('image')) {
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $mediaAssets = [];

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $mediaAssets[] = ['type' => 'image', 'url' => 'storage/' . $path, 'path' => $path];
            }
        }

        if (!empty($validated['video_links'])) {
            foreach ($validated['video_links'] as $link) {
                if ($link) {
                    $mediaAssets[] = ['type' => 'video', 'url' => $link];
                }
            }
        }

        $product->media_assets = $mediaAssets;
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        $product->name = $validated['name'];
        $product->description = $validated['description'] ?? '';
        $product->base_price = $validated['base_price'];
        $product->current_stock = $validated['current_stock'];

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $mediaAssets = $product->media_assets ?? [];

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $mediaAssets[] = ['type' => 'image', 'url' => 'storage/' . $path, 'path' => $path];
            }
        }

        if (!empty($validated['video_links'])) {
            foreach ($validated['video_links'] as $link) {
                if ($link) {
                    $mediaAssets[] = ['type' => 'video', 'url' => $link];
                }
            }
        }

        $product->media_assets = $mediaAssets;
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroyMedia(Product $product, int $index)
    {
        $mediaAssets = $product->media_assets ?? [];
        if (isset($mediaAssets[$index])) {
            $item = $mediaAssets[$index];
            if ($item['type'] === 'image' && isset($item['path'])) {
                Storage::disk('public')->delete($item['path']);
            }
            array_splice($mediaAssets, $index, 1);
            $product->media_assets = $mediaAssets;
            $product->save();
        }

        return redirect()->back()->with('success', 'Media berhasil dihapus!');
    }

    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        
        $mediaAssets = $product->media_assets ?? [];
        foreach ($mediaAssets as $media) {
            if ($media['type'] === 'image' && isset($media['path'])) {
                Storage::disk('public')->delete($media['path']);
            }
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus!');
    }
}
