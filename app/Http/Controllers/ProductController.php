<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $products = $query->orderBy('name')->paginate(10);

        if ($request->header('HX-Target') === 'products-list') {
            return view('products.products-list', compact('products'));
        }
        return view('products.products-index', compact('products'));
    }

    public function create(): View
    {
        $product = new Product();
        return view('products.products-form', compact('product'));
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        $product = new Product();
        $product->name = $validated['name'];
        $product->description = $validated['description'];
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

        return response()
            ->view('products.products-list', ['products' => Product::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'close-drawer' => true,
                'toast' => ['message' => 'Produk berhasil ditambahkan!', 'type' => 'success'],
            ]));
    }

    public function edit(Product $product): View
    {
        return view('products.products-form', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        $product->name = $validated['name'];
        $product->description = $validated['description'];
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

        return response()
            ->view('products.products-list', ['products' => Product::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'close-drawer' => true,
                'toast' => ['message' => 'Produk berhasil diperbarui!', 'type' => 'success'],
            ]));
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

        return response()
            ->view('products.products-form', compact('product'))
            ->header('HX-Trigger', json_encode([
                'toast' => ['message' => 'Media berhasil dihapus!', 'type' => 'success'],
            ]));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()
            ->view('products.products-list', ['products' => Product::orderBy('name')->paginate(10)])
            ->header('HX-Trigger', json_encode([
                'toast' => ['message' => 'Produk berhasil dihapus!', 'type' => 'success'],
            ]));
    }
}
