<div class="px-4 sm:px-4 py-3 flex items-start justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 sticky top-0 z-10">
    <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">{{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}</h2>
    <button type="button" @click="drawerOpen = false" class="rounded p-1 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
</div>
<div class="px-4 sm:px-4 py-2.5 pb-20">
    <form hx-post="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" 
          hx-encoding="multipart/form-data" 
          hx-target="#products-list" 
          hx-swap="innerHTML" 
          class="space-y-4" 
          x-data="{ submitting: false, videoLinks: [], mainImagePreview: null, galleryPreviews: [] }" 
          @submit="submitting = true" 
          @htmx:after-request="submitting = false">
        
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Produk</label>
            <input type="text" name="name" value="{{ $product->name }}" required class="w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat</label>
            <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">{{ $product->description }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Harga Jual (Rp)</label>
                <input type="number" name="base_price" value="{{ intval($product->base_price) ?? 0 }}" min="0" required class="w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Stok Produk</label>
                <input type="number" name="current_stock" value="{{ $product->current_stock ?? 0 }}" min="0" required class="w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
            </div>
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-3">Media Produk</h3>

            <!-- Existing Main Image -->
            @if($product->image_path)
            <div class="mb-3" x-show="!mainImagePreview">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Gambar Utama Tersimpan</label>
                <img src="{{ asset('storage/' . $product->image_path) }}" class="w-24 h-24 object-cover rounded-md border border-gray-200 dark:border-gray-700 shadow-sm">
            </div>
            @endif

            <!-- Main Image Input & Preview -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ $product->image_path ? 'Ganti Gambar Utama' : 'Upload Gambar Utama' }}</label>
                <input type="file" name="image" accept="image/*" @change="mainImagePreview = URL.createObjectURL($event.target.files[0])" class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-400 transition cursor-pointer">
                <div x-show="mainImagePreview" class="mt-2" style="display: none;">
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Preview Baru</label>
                    <img :src="mainImagePreview" class="w-24 h-24 object-cover rounded-md border border-gray-200 dark:border-gray-700 shadow-sm">
                </div>
            </div>

            <!-- Existing Gallery/Media -->
            @if($product->exists && !empty($product->media_assets))
            <div class="mb-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Galeri & Video Tersimpan</label>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                    @foreach($product->media_assets as $index => $media)
                    <div class="relative group aspect-square bg-gray-100 dark:bg-gray-800 rounded-md overflow-hidden border border-gray-200 dark:border-gray-700">
                        @if($media['type'] === 'image')
                            <img src="{{ asset($media['url']) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-2 text-center text-red-500">
                                <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                                <span class="text-[8px] text-gray-500 dark:text-gray-400 break-all leading-tight">{{ \Illuminate\Support\Str::limit($media['url'], 20) }}</span>
                            </div>
                        @endif
                        <button type="button" hx-delete="{{ route('products.media.destroy', [$product, $index]) }}" hx-confirm="Hapus media ini dari galeri?" hx-target="#drawer-content" hx-swap="innerHTML" class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded opacity-0 group-hover:opacity-100 transition shadow hover:bg-red-600 focus:opacity-100">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- New Gallery Images & Preview -->
            <div class="mb-4">
                <x-multi-file-upload name="gallery_images[]" label="Tambah Foto Galeri (Drag & Drop didukung)" accept="image/*" />
            </div>

            <!-- New Video Links -->
            <div class="mb-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Tambah Tautan Video</label>
                <template x-for="(link, index) in videoLinks" :key="index">
                    <div class="flex gap-2 mb-2">
                        <input type="url" name="video_links[]" placeholder="https://youtube.com/..." class="flex-1 px-3 py-1.5 text-sm rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 outline-none">
                        <button type="button" @click="videoLinks.splice(index, 1)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="videoLinks.push('')" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary-600 dark:text-primary-400 hover:underline">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Kolom Video
                </button>
            </div>
        </div>

        <div class="fixed bottom-0 right-0 w-full max-w-md bg-white dark:bg-gray-950 p-3 border-t border-gray-200 dark:border-gray-800 z-20">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 rounded-md text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 transition" x-bind:disabled="submitting">
                <span x-show="!submitting">Simpan Produk</span>
                <span x-show="submitting" style="display: none;">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>

