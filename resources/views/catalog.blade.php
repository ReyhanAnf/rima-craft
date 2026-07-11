<x-layouts.public>
    
    <!-- Hero Section (Edge-to-Edge) -->
    <div class="relative w-full h-[85vh] min-h-[600px] flex items-center justify-center overflow-hidden -mt-20 pt-20 transition-colors duration-500">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 z-0 bg-gray-100 dark:bg-gray-900 transition-colors duration-500">
            @php
                $heroUrl = config('settings.hero_image_url') ? asset('storage/' . config('settings.hero_image_url')) : asset('assets/landing/hero.png');
            @endphp
            <img src="{{ $heroUrl }}" alt="Rima Craft Background" class="w-full h-full object-cover object-center opacity-70 dark:opacity-80 mix-blend-multiply dark:mix-blend-overlay transition-opacity duration-500" />
            <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/70 to-white/10 dark:from-black/90 dark:via-black/60 dark:to-transparent transition-colors duration-500"></div>
        </div>
        
        <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-start justify-center">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-bold text-gray-900 dark:text-white tracking-wide mb-6 leading-[1.1] max-w-3xl drop-shadow-sm dark:drop-shadow-xl transition-colors duration-500">
                {{ config('settings.hero_title_1', 'Seni Anyaman Tradisional') }}<br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-amber-400 dark:from-amber-300 dark:to-amber-500">{{ config('settings.hero_title_2', 'Bercita Rasa Modern') }}</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-700 dark:text-gray-200 max-w-2xl font-medium mb-10 leading-relaxed drop-shadow-sm dark:drop-shadow-lg transition-colors duration-500">
                {{ config('settings.hero_description', 'Temukan koleksi kerajinan eksklusif Rima Craft. Diproses secara manual oleh pengrajin lokal berdedikasi untuk menghasilkan karya seni berkualitas tinggi.') }}
            </p>
            <div class="flex flex-wrap items-center gap-4 justify-center md:justify-start">
                <a href="#katalog" class="px-8 py-3.5 bg-amber-500 hover:bg-amber-600 text-white dark:text-gray-950 font-bold rounded-full transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2 hover:scale-105 active:scale-95">
                    Mulai Belanja
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Cinematic Video Showcase Section -->
    <section id="cinematic-showcase" class="bg-[#f7f3ed] dark:bg-gray-950 overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-5 min-h-[70vh] lg:min-h-[85vh]">
            <!-- Text Content -->
            <div class="flex flex-col justify-center px-8 py-20 lg:py-28 lg:px-16 xl:px-24 order-2 lg:order-1 lg:col-span-3">
                <div class="mb-10">
                    <p class="text-sm text-amber-700 dark:text-amber-400 mb-5 flex items-center gap-3">
                        <span class="block h-px w-10 bg-amber-400"></span>
                        <span>Cara kami bekerja</span>
                    </p>
                    <h2 class="max-w-lg">
                        <span class="block font-serif italic text-3xl md:text-4xl text-gray-800 dark:text-gray-100 leading-snug mb-1">Proses anyaman</span>
                        <span class="block text-2xl md:text-3xl font-semibold text-gray-900 dark:text-white tracking-tight leading-snug">dari bahan mentah ke produk jadi.</span>
                    </h2>
                </div>

                <p class="text-[15px] md:text-base text-gray-600 dark:text-gray-300 leading-[1.8] max-w-md mb-14">
                    Rotan dan pandan diolah oleh pengrajin lokal. Dikupas, dikeringkan, lalu dianyam satu per satu.
                </p>

                <div class="grid sm:grid-cols-2 gap-8 max-w-lg">
                    <div class="border-l-2 border-amber-400 pl-5">
                        <span class="text-[11px] font-medium text-amber-600 dark:text-amber-400 tabular-nums mb-2 block">01</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1.5">Anyaman manual</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Setiap produk dianyam tangan, tanpa mesin produksi massal.</p>
                    </div>
                    <div class="border-l-2 border-stone-300 dark:border-stone-600 pl-5">
                        <span class="text-[11px] font-medium text-stone-500 dark:text-stone-400 tabular-nums mb-2 block">02</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1.5">Sortir bahan</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Bahan diseleksi sebelum masuk proses anyaman.</p>
                    </div>
                </div>
            </div>

            <!-- Video Content -->
            <div class="relative w-full h-[60vh] md:h-[70vh] lg:h-auto order-1 lg:order-2 bg-gray-100 dark:bg-gray-900 lg:col-span-2">
                @php
                    $loopingVideoUrl = config('settings.looping_video_url');
                    if ($loopingVideoUrl && !str_starts_with($loopingVideoUrl, 'http')) {
                        $loopingVideoUrl = asset('storage/' . $loopingVideoUrl);
                    }
                @endphp
                
                @if($loopingVideoUrl)
                    <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover object-center">
                        <source src="{{ $loopingVideoUrl }}" type="video/mp4">
                    </video>
                @else
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span class="text-gray-400 dark:text-gray-600 font-bold uppercase tracking-widest text-xs">Video Belum Diunggah</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Image Gallery -->
    <section class="py-16 md:py-24 bg-white dark:bg-gray-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10 md:mb-14 md:flex md:items-end md:justify-between md:gap-12">
                <div class="max-w-md">
                    <p class="text-sm text-amber-700 dark:text-amber-400 mb-5 flex items-center gap-3">
                        <span class="block h-px w-10 bg-amber-400"></span>
                        <span>Galeri</span>
                    </p>
                    <h2 class="max-w-sm">
                        <span class="block font-serif italic text-2xl md:text-3xl text-gray-800 dark:text-gray-100 leading-snug mb-1">Foto workshop</span>
                        <span class="block text-xl md:text-2xl font-semibold text-gray-900 dark:text-white tracking-tight leading-snug">dan produk jadi.</span>
                    </h2>
                </div>
                <p class="mt-6 md:mt-0 text-[15px] text-gray-500 dark:text-gray-400 leading-relaxed max-w-xs md:text-right">
                    Dokumentasi proses anyaman dan hasil karya dari workshop kami.
                </p>
            </div>

            @if(isset($galleries) && $galleries->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-x-5 md:gap-y-10">
                    @foreach($galleries as $i => $gallery)
                        @php
                            $isWide = $i === 0 || $i % 4 === 3;
                            $aspect = $i === 0 ? 'aspect-[16/10] sm:aspect-[21/9]' : ($i % 3 === 1 ? 'aspect-[3/4]' : 'aspect-[4/3]');
                            $colSpan = $isWide ? 'sm:col-span-2 lg:col-span-2' : '';
                        @endphp
                        <figure class="group {{ $colSpan }}">
                            <div class="overflow-hidden bg-stone-100 dark:bg-gray-800 {{ $aspect }}">
                                <img
                                    src="{{ asset('storage/' . $gallery->image_url) }}"
                                    alt="{{ $gallery->title ?? 'Galeri Rima Craft' }}"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                    loading="lazy"
                                >
                            </div>
                            @if($gallery->label || $gallery->title)
                                <figcaption class="pt-3 flex items-baseline justify-between gap-3 px-0.5">
                                    @if($gallery->title)
                                        <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">{{ $gallery->title }}</p>
                                    @endif
                                    @if($gallery->label)
                                        <span class="text-[11px] text-stone-400 dark:text-stone-500 shrink-0">{{ $gallery->label }}</span>
                                    @endif
                                </figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            @else
                <div class="py-16 md:py-20 border border-dashed border-stone-200 dark:border-stone-700 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada foto galeri.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Video Storytelling Section -->
    <section class="py-16 md:py-24 bg-stone-50 dark:bg-gray-950">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 md:mb-10 md:flex md:items-end md:justify-between md:gap-12">
                <div class="max-w-md">
                    <p class="text-sm text-amber-700 dark:text-amber-400 mb-5 flex items-center gap-3">
                        <span class="block h-px w-10 bg-amber-400"></span>
                        <span>Video proses</span>
                    </p>
                    <h2 class="max-w-sm">
                        <span class="block font-serif italic text-2xl md:text-3xl text-gray-800 dark:text-gray-100 leading-snug mb-1">Proses di workshop</span>
                        <span class="block text-xl md:text-2xl font-semibold text-gray-900 dark:text-white tracking-tight leading-snug">dalam video singkat.</span>
                    </h2>
                </div>
                <p class="mt-6 md:mt-0 text-[15px] text-gray-500 dark:text-gray-400 leading-relaxed max-w-xs md:text-right">
                    Cuplikan pekerjaan anyaman langsung dari bengkel pengrajin kami.
                </p>
            </div>

            <div class="relative aspect-video w-full overflow-hidden bg-stone-900 border border-stone-200 dark:border-stone-700">
                <iframe
                    class="absolute inset-0 w-full h-full"
                    src="{{ config('settings.video_url', 'https://www.youtube.com/embed/ScMzIvxBSi4') }}?rel=0&modestbranding=1"
                    title="Video proses anyaman Rima Craft"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
    </section>

    <!-- Product Grid (Katalog) -->
    <section id="katalog" class="py-24 lg:py-32 bg-gray-50 dark:bg-[#0a0a0a] border-t border-gray-200 dark:border-[#1a1a1a] transition-colors duration-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center text-center mb-20">
                <span class="text-amber-600 dark:text-amber-500 font-medium tracking-[0.3em] uppercase text-[10px] md:text-xs mb-4 block">Masterpiece</span>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-gray-900 dark:text-white tracking-wide mb-6 transition-colors">Koleksi Eksklusif</h2>
                <div class="w-16 h-px bg-amber-500/50 mb-6"></div>
                <p class="text-lg text-gray-600 dark:text-gray-400 font-light max-w-2xl mx-auto transition-colors">Pilih dan miliki karya seni fungsional favorit Anda. Setiap detail merepresentasikan keahlian tingkat tinggi dan warisan tak ternilai.</p>
            </div>

            {{-- Product Filter --}}
            @include('catalog.partials.product-filter')

            <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 transition-opacity duration-300 [&.htmx-request]:opacity-50">
                @include('catalog.products-grid', ['products' => $products])
            </div>
        </div>
    </section>

    <!-- Lokasi & Kontak Section -->
    <section id="kontak" class="py-24 lg:py-32 bg-gray-50 dark:bg-[#050505] relative overflow-hidden transition-colors duration-500">
        <!-- Subtle gradient separator -->
        <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-amber-500/20 to-transparent"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 lg:gap-12 items-center">
                
                <!-- Contact Details (Left side) -->
                <div class="lg:col-span-5 order-2 lg:order-1 relative z-10">
                    <span class="text-amber-600 dark:text-amber-500 font-medium tracking-[0.25em] uppercase text-xs mb-6 block transition-colors">Terhubung Dengan Kami</span>
                    <h2 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-gray-900 dark:text-white mb-12 leading-tight transition-colors">
                        Kunjungi<br/>Workshop Kami
                    </h2>
                    
                    <div class="space-y-12">
                        <!-- Alamat -->
                        <div class="group">
                            <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4 group-hover:text-amber-600 dark:group-hover:text-amber-500 transition-colors">Alamat Workshop & Studio</h4>
                            <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed font-light max-w-sm transition-colors">{{ config('settings.address', 'Alamat belum diatur') }}</p>
                        </div>
                        
                        <!-- Jam Operasional -->
                        @php $businessHours = trim((string) config('settings.business_hours', '')); @endphp
                        @if($businessHours !== '')
                        <div class="group">
                            <h4 class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-[0.2em] mb-4">Jam Operasional</h4>
                            <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed font-light transition-colors">{{ $businessHours }}</p>
                        </div>
                        @endif

                        <!-- Divider -->
                        <div class="w-12 h-px bg-gray-800"></div>
                        
                        <!-- Contact Links -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-10">
                            @php
                                $rawPhone    = (string) config('settings.business_phone', '');
                                $digitsOnly  = preg_replace('/\D/', '', $rawPhone);
                                if (str_starts_with($digitsOnly, '0')) {
                                    $digitsOnly = '62' . substr($digitsOnly, 1);
                                }
                                $formattedPhone = $digitsOnly;
                            @endphp
                            @if(strlen($formattedPhone) >= 10)
                            <div>
                                <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">WhatsApp</h4>
                                <a href="https://wa.me/{{ $formattedPhone }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="Chat WhatsApp dengan Rima Craft"
                                   class="inline-flex items-center gap-2 px-6 py-3 bg-[#25D366] hover:bg-[#128C7E] text-white font-bold rounded-xl shadow-lg shadow-[#25D366]/20 transition-all hover:scale-105 active:scale-95">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    Hubungi Kami
                                </a>
                            </div>
                            @endif
                            
                            @if(config('settings.email'))
                            <div>
                                <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">Email</h4>
                                <a href="mailto:{{ config('settings.email') }}" class="text-lg text-gray-700 dark:text-white font-light hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                    {{ config('settings.email') }}
                                </a>
                            </div>
                            @endif
                            
                            @if(config('settings.instagram'))
                            <div class="sm:col-span-2">
                                <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">Instagram</h4>
                                <a href="https://instagram.com/{{ config('settings.instagram') }}" target="_blank" class="text-lg text-gray-700 dark:text-white font-light hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                    {{ '@' . config('settings.instagram') }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Map (Right side) -->
                <div class="lg:col-span-7 order-1 lg:order-2">
                    <div class="relative w-full h-[450px] lg:h-[650px] rounded-[2rem] overflow-hidden ring-1 ring-gray-200 dark:ring-white/5 shadow-2xl group transition-all duration-500">
                        @php $gmaps = config('settings.gmaps_iframe'); @endphp
                        @if($gmaps)
                            <iframe src="{{ $gmaps }}" class="absolute inset-0 w-full h-full border-0 transition-transform duration-1000 group-hover:scale-105" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            <!-- Vignette effect -->
                            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_transparent_50%,_#f9fafb_120%)] dark:bg-[radial-gradient(ellipse_at_center,_transparent_50%,_#050505_120%)] pointer-events-none transition-colors duration-500"></div>
                        @else
                            <div class="absolute inset-0 bg-gray-100 dark:bg-[#0a0a0a] flex flex-col items-center justify-center text-gray-400 dark:text-gray-600 border border-gray-200 dark:border-gray-800 rounded-[2rem] transition-colors duration-500">
                                <span class="font-serif italic text-xl">Peta Belum Diatur</span>
                            </div>
                        @endif
                    </div>
                </div>
                
            </div>
        </div>
    </section>

</x-layouts.public>
