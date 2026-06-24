{{-- resources/views/catalog/products-grid.blade.php --}}
{{-- Partial view — fragment HTML murni, tanpa @extends / layout wrapper --}}
{{-- Digunakan: @include dari catalog.blade.php + HTMX response dari CatalogController@filter --}}

@if($products->isEmpty())
    <div class="col-span-full flex flex-col items-center justify-center py-32 text-center">
        <svg class="w-16 h-16 text-gray-300 dark:text-gray-800 mb-6 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <h3 class="text-xl font-serif font-bold text-gray-400 dark:text-gray-600 mb-3 tracking-wide">Tidak Ada Produk</h3>
        <p class="text-gray-500 dark:text-gray-600 max-w-md mx-auto text-sm font-light">Tidak ada produk yang sesuai dengan filter yang dipilih.</p>
    </div>
@else
    @foreach($products as $product)
        <div class="group flex flex-col relative bg-white dark:bg-[#0d0d0d] rounded-3xl overflow-hidden transition-all duration-300 hover:bg-gray-50 dark:hover:bg-[#111111] shadow-sm hover:shadow-lg border border-gray-100 dark:border-white/5" x-data="{ detailOpen: false }">

            @php
                $slides = [];
                if ($product->image_path) {
                    $slides[] = ['type' => 'image', 'url' => asset('storage/' . $product->image_path)];
                }
                if (!empty($product->media_assets)) {
                    foreach($product->media_assets as $media) {
                        $url = $media['url'];
                        if ($media['type'] === 'image') {
                            $url = asset($url);
                        } elseif ($media['type'] === 'video') {
                            if (str_contains($url, 'youtube.com/watch?v=')) {
                                $url = str_replace('watch?v=', 'embed/', $url);
                                $url = explode('&', $url)[0];
                            } elseif (str_contains($url, 'youtu.be/')) {
                                $url = str_replace('youtu.be/', 'youtube.com/embed/', $url);
                                $url = explode('?', $url)[0];
                            }
                        }
                        $slides[] = ['type' => $media['type'], 'url' => $url];
                    }
                }
            @endphp

            <!-- Product Media Carousel -->
            <div class="aspect-[4/5] w-full relative overflow-hidden bg-gray-100 dark:bg-[#0a0a0a] group/carousel cursor-pointer" x-data="{ activeSlide: 0, slidesCount: {{ count($slides) }} }" @click="detailOpen = true">
                <!-- Badge Stok Habis -->
                @if($product->current_stock <= 0)
                    <div class="absolute top-4 right-4 z-20 px-4 py-1.5 bg-white/90 dark:bg-black/80 text-gray-900 dark:text-white text-[9px] font-bold uppercase tracking-[0.2em] backdrop-blur-md border border-gray-200 dark:border-white/10">
                        Sold Out
                    </div>
                @endif

                @if(count($slides) > 0)
                    <div class="w-full h-full flex transition-transform duration-700 ease-[cubic-bezier(0.25,1,0.5,1)]" :style="'transform: translateX(-' + (activeSlide * 100) + '%)'">
                        @foreach($slides as $index => $slide)
                            <div class="w-full h-full flex-shrink-0 relative">
                                @if($slide['type'] === 'image')
                                    <img src="{{ $slide['url'] }}" alt="{{ $product->name }}" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity duration-500">
                                @elseif($slide['type'] === 'video')
                                    <iframe src="{{ $slide['url'] }}" class="w-full h-full pointer-events-auto filter grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-700" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(count($slides) > 1)
                        <button @click.prevent.stop="activeSlide = activeSlide > 0 ? activeSlide - 1 : slidesCount - 1" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white p-2 opacity-0 group-hover/carousel:opacity-100 transition-all z-20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <button @click.prevent.stop="activeSlide = activeSlide < slidesCount - 1 ? activeSlide + 1 : 0" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white p-2 opacity-0 group-hover/carousel:opacity-100 transition-all z-20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"></path></svg>
                        </button>

                        <!-- Minimalist dots -->
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2 z-20 pointer-events-none opacity-0 group-hover/carousel:opacity-100 transition-opacity duration-300">
                            <template x-for="i in slidesCount">
                                <div class="h-[2px] transition-all duration-300" :class="(i - 1) === activeSlide ? 'w-6 bg-white' : 'w-2 bg-white/40'"></div>
                            </template>
                        </div>
                    @endif
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-800">
                        <span class="font-serif italic">Tanpa Gambar</span>
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="flex flex-col text-center p-6 lg:p-8">
                <h3 class="text-xl font-serif font-bold text-gray-900 dark:text-white mb-3 cursor-pointer hover:text-amber-600 dark:hover:text-amber-500 transition-colors" @click="detailOpen = true">{{ $product->name }}</h3>
                <div class="text-sm text-gray-500 dark:text-gray-500 font-light mb-5 line-clamp-1">
                    {{ $product->description ?: 'Kerajinan pengrajin lokal' }}
                </div>

                <div class="text-xl font-medium text-amber-600 dark:text-amber-500 tracking-wide mb-8">
                    Rp {{ number_format($product->base_price, 0, ',', '.') }}
                </div>

                <!-- Always Visible Actions -->
                <div class="flex items-center justify-center gap-3 w-full mt-auto" @click.stop>
                    <button
                        @click.prevent="detailOpen = true"
                        class="flex-1 py-3 rounded-xl border border-gray-300 dark:border-gray-800 text-gray-600 dark:text-gray-300 text-[10px] font-bold uppercase tracking-widest transition-all duration-300 hover:border-gray-900 hover:text-gray-900 dark:hover:border-white dark:hover:text-white"
                    >
                        Detail
                    </button>

                    @if($product->current_stock > 0)
                        <button
                            @click.prevent="$store.cart.add({ id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $product->base_price }}, stock: {{ $product->current_stock }}, image: '{{ $product->image_path ? asset('storage/' . $product->image_path) : '' }}' })"
                            class="flex-1 py-3 rounded-xl bg-gray-900 dark:bg-white text-white dark:text-black text-[10px] font-bold uppercase tracking-widest transition-all duration-300 hover:bg-amber-600 dark:hover:bg-amber-500 hover:text-white dark:hover:text-white"
                        >
                            Beli
                        </button>
                    @else
                        <button disabled class="flex-1 py-3 rounded-xl bg-gray-100 dark:bg-[#1a1a1a] text-gray-400 dark:text-gray-600 text-[10px] font-bold uppercase tracking-widest cursor-not-allowed">
                            Habis
                        </button>
                    @endif
                </div>
            </div>

            <!-- Product Detail Side Drawer -->
            <template x-teleport="body">
                <div x-show="detailOpen" style="display: none;" class="fixed inset-0 z-[100] overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
                    <div class="absolute inset-0 overflow-hidden">
                        <!-- Backdrop -->
                        <div x-show="detailOpen" x-transition.opacity.duration.500ms class="absolute inset-0 bg-white/80 dark:bg-black/80 backdrop-blur-sm transition-opacity" @click="detailOpen = false"></div>

                        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full md:pl-10">
                            <!-- Slide-over panel -->
                            <div x-show="detailOpen"
                                 x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                                 x-transition:enter-start="translate-x-full"
                                 x-transition:enter-end="translate-x-0"
                                 x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                                 x-transition:leave-start="translate-x-0"
                                 x-transition:leave-end="translate-x-full"
                                 class="pointer-events-auto w-screen max-w-2xl">

                                <div class="flex h-full flex-col bg-white dark:bg-[#050505] border-l border-gray-200 dark:border-gray-800 shadow-2xl overflow-y-auto custom-scrollbar relative">

                                    <!-- Close button -->
                                    <button @click="detailOpen = false" class="absolute top-6 right-6 z-50 p-3 bg-white/40 dark:bg-black/40 text-gray-900 dark:text-white hover:bg-amber-500 dark:hover:bg-amber-500 hover:text-white rounded-full backdrop-blur-md transition-all hover:scale-110 shadow-lg">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>

                                    <!-- Top side: Image Carousel within Drawer -->
                                    <div class="w-full h-[40vh] md:h-[50vh] shrink-0 bg-gray-100 dark:bg-[#111] relative overflow-hidden" x-data="{ modalSlide: 0, modalSlidesCount: {{ count($slides) }} }">
                                        @if(count($slides) > 0)
                                            <div class="w-full h-full flex transition-transform duration-700 ease-[cubic-bezier(0.25,1,0.5,1)]" :style="'transform: translateX(-' + (modalSlide * 100) + '%)'">
                                                @foreach($slides as $index => $slide)
                                                    <div class="w-full h-full flex-shrink-0 relative">
                                                        @if($slide['type'] === 'image')
                                                            <img src="{{ $slide['url'] }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                        @elseif($slide['type'] === 'video')
                                                            <iframe src="{{ $slide['url'] }}" class="w-full h-full pointer-events-auto" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if(count($slides) > 1)
                                                <button @click.prevent.stop="modalSlide = modalSlide > 0 ? modalSlide - 1 : modalSlidesCount - 1" class="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-amber-500 p-2 transition-all z-20 mix-blend-difference">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"></path></svg>
                                                </button>
                                                <button @click.prevent.stop="modalSlide = modalSlide < modalSlidesCount - 1 ? modalSlide + 1 : 0" class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-amber-500 p-2 transition-all z-20 mix-blend-difference">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"></path></svg>
                                                </button>
                                                <!-- Minimalist dots -->
                                                <div class="absolute bottom-6 left-0 right-0 flex justify-center gap-2 z-20 pointer-events-none">
                                                    <template x-for="i in modalSlidesCount">
                                                        <div class="h-[2px] transition-all duration-300" :class="(i - 1) === modalSlide ? 'w-8 bg-amber-500' : 'w-3 bg-white/70'"></div>
                                                    </template>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Bottom side: Content -->
                                    <div class="flex-1 p-8 md:p-12 flex flex-col justify-start">
                                        <span class="text-amber-600 dark:text-amber-500 font-medium tracking-[0.3em] uppercase text-[10px] md:text-xs mb-3 md:mb-4 block">Detail Karya</span>
                                        <h2 class="text-3xl md:text-5xl font-serif font-bold text-gray-900 dark:text-white mb-4 leading-tight">{{ $product->name }}</h2>
                                        <div class="text-2xl font-medium text-gray-900 dark:text-white tracking-wide mb-8">
                                            Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                        </div>

                                        <div class="w-12 h-px bg-gray-200 dark:bg-gray-800 mb-8"></div>

                                        <div class="text-sm md:text-base text-gray-600 dark:text-gray-400 font-light leading-relaxed mb-10 space-y-4">
                                            {!! nl2br(e($product->description)) !!}
                                        </div>

                                        <div class="space-y-4 text-xs md:text-sm font-light text-gray-500 mb-10 mt-auto">
                                            <div class="flex items-center justify-between py-4 border-t border-b border-gray-200 dark:border-gray-800">
                                                <span class="uppercase tracking-widest text-[10px] md:text-xs text-gray-500">Status Stok</span>
                                                @if($product->current_stock > 0)
                                                    <span class="text-gray-900 dark:text-white font-medium">{{ $product->current_stock }} Kerajinan Tersedia</span>
                                                @else
                                                    <span class="text-red-500 font-bold">Terjual Habis</span>
                                                @endif
                                            </div>
                                        </div>

                                        <button
                                            @click="$store.cart.add({ id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $product->base_price }}, stock: {{ $product->current_stock }}, image: '{{ $product->image_path ? asset('storage/' . $product->image_path) : '' }}' }); detailOpen = false;"
                                            class="w-full py-5 border border-gray-900 dark:border-white text-gray-900 dark:text-white text-xs font-bold uppercase tracking-[0.2em] transition-all duration-300 hover:bg-gray-900 hover:text-white dark:hover:bg-white dark:hover:text-black disabled:opacity-50 disabled:cursor-not-allowed"
                                            {{ $product->current_stock <= 0 ? 'disabled' : '' }}
                                        >
                                            @if($product->current_stock > 0)
                                                Masukkan Ke Keranjang
                                            @else
                                                Stok Habis
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    @endforeach
@endif
