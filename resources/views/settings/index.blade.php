<x-layouts.app title="Pengaturan Web">
    <div x-data="{ tab: 'umum' }">
        <!-- Header & Tabs -->
        <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-2">Pengaturan Web</h2>
                <p class="text-gray-500 dark:text-gray-400">Atur konten informasi bisnis dan Landing Page.</p>
            </div>
            
            <div class="inline-flex bg-gray-100 dark:bg-gray-800 p-1 rounded-md shadow-sm">
                <button @click="tab = 'umum'" 
                        :class="tab === 'umum' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-4 py-2 text-sm font-semibold rounded-md transition-all">
                    Data Umum
                </button>
                <button @click="tab = 'landing'" 
                        :class="tab === 'landing' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-4 py-2 text-sm font-semibold rounded-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Landing Page
                </button>
                <button @click="tab = 'seo'" 
                        :class="tab === 'seo' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-4 py-2 text-sm font-semibold rounded-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    SEO & Meta
                </button>
                <button @click="tab = 'info'" 
                        :class="tab === 'info' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-4 py-2 text-sm font-semibold rounded-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Halaman Info
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-50 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 p-4 rounded-md border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- ==================== TAB UMUM ==================== -->
            <div x-show="tab === 'umum'" x-transition.opacity.duration.300ms class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 shadow-sm p-4 md:p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Informasi Bisnis</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Bisnis</label>
                        <input type="text" name="business_name" value="{{ $settings['business_name'] ?? 'Rima Craft' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor WhatsApp Utama</label>
                        <input type="text" name="business_phone" value="{{ $settings['business_phone'] ?? '6281234567890' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Contoh: 6281234567890">
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-100 dark:border-gray-800 pt-6">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 text-sm">Informasi Kontak & Lokasi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" value="{{ $settings['email'] ?? '' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Contoh: info@rimacraft.com">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Instagram Handle</label>
                            <input type="text" name="instagram" value="{{ $settings['instagram'] ?? '' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Contoh: rimacraft_id">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap Workshop</label>
                            <textarea name="address" rows="3" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Masukkan alamat lengkap workshop...">{{ $settings['address'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Google Maps Embed URL (SRC)</label>
                            <input type="text" name="gmaps_iframe" value="{{ $settings['gmaps_iframe'] ?? '' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Contoh: https://www.google.com/maps/embed?pb=...">
                            <p class="text-[10px] text-gray-500 mt-1">Buka Google Maps > Bagikan > Sematkan Peta > Copy isi dari <code>src="..."</code></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Jam Operasional</label>
                            <input
                                type="text"
                                name="business_hours"
                                value="{{ $settings['business_hours'] ?? '' }}"
                                class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none"
                                placeholder="Contoh: Senin–Sabtu, 08.00–17.00 WIB"
                            />
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-md shadow-sm transition-all text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </div>

            <!-- ==================== TAB LANDING PAGE ==================== -->
            <div x-show="tab === 'landing'" x-transition.opacity.duration.300ms style="display: none;" class="space-y-6">
                
                <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 shadow-sm p-4 md:p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Hero Section (Bagian Atas)</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Gambar Latar (Hero Background)</label>
                            <div class="flex items-center gap-3">
                                <input type="file" name="hero_image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-400">
                                @if(isset($settings['hero_image_url']))
                                    <img src="{{ asset('storage/' . $settings['hero_image_url']) }}" class="w-10 h-10 object-cover rounded-md shadow-sm">
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Badge (Teks Kecil di atas Judul)</label>
                            <input type="text" name="hero_badge" value="{{ $settings['hero_badge'] ?? 'Karya Autentik Nusantara' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Judul Utama (Baris 1)</label>
                                <input type="text" name="hero_title_1" value="{{ $settings['hero_title_1'] ?? 'Seni Anyaman Tradisional' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Judul Utama (Baris 2 - Warna Emas)</label>
                                <input type="text" name="hero_title_2" value="{{ $settings['hero_title_2'] ?? 'Bercita Rasa Modern' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat</label>
                            <textarea name="hero_description" rows="3" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">{{ $settings['hero_description'] ?? 'Temukan koleksi kerajinan eksklusif Rima Craft. Diproses secara manual oleh pengrajin lokal berdedikasi untuk menghasilkan karya seni berkualitas tinggi.' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 shadow-sm p-4 md:p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Media Pendukung</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">File Video Pendek (.mp4 untuk Looping Background)</label>
                            <div class="flex items-center gap-3">
                                <input type="file" name="looping_video" accept="video/mp4" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-500/10 dark:file:text-primary-400">
                                @if(isset($settings['looping_video_url']) && !str_starts_with($settings['looping_video_url'], 'http'))
                                    <div class="text-xs text-emerald-600 font-bold whitespace-nowrap">✔ File Tersimpan</div>
                                @endif
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Gunakan video pendek tanpa suara (B-Roll) untuk menambah estetika pameran produk. Max 10MB.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">URL Video Pendek Eksternal (.mp4 opsional)</label>
                            <input type="text" name="looping_video_url" value="{{ $settings['looping_video_url'] ?? '' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Contoh: https://domain.com/video.mp4">
                            <p class="text-[10px] text-gray-500 mt-1">Gunakan jika video dihosting di luar server.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">URL YouTube Video (Di Balik Layar)</label>
                            <input type="text" name="video_url" value="{{ $settings['video_url'] ?? 'https://www.youtube.com/embed/ScMzIvxBSi4' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Contoh: https://www.youtube.com/embed/XXXXX">
                            <p class="text-[10px] text-gray-500 mt-1">Gunakan format embed URL YouTube (https://www.youtube.com/embed/ID_VIDEO).</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-md shadow-sm transition-all text-sm">
                        Simpan Perubahan Landing Page
                    </button>
                </div>
            </div>

            <!-- ==================== TAB SEO ==================== -->
            <div x-show="tab === 'seo'" x-transition.opacity.duration.300ms style="display: none;" class="space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 shadow-sm p-4 md:p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Optimasi Mesin Pencari (SEO)</h3>
                    <p class="text-xs text-gray-500 mb-6">Informasi ini akan dibaca oleh Google saat mengindeks website Anda. Sangat penting untuk pemasaran digital.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">SEO Title (Judul Halaman Utama)</label>
                            <input type="text" name="seo_title" value="{{ $settings['seo_title'] ?? config('settings.business_name', 'Rima Craft') . ' - Katalog Produk' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                            <p class="text-[10px] text-gray-500 mt-1">Muncul di tab browser dan hasil pencarian Google. Usahakan max 60 karakter.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">SEO Meta Description</label>
                            <textarea name="seo_description" rows="3" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">{{ $settings['seo_description'] ?? 'Katalog eksklusif kerajinan tangan dari Rima Craft.' }}</textarea>
                            <p class="text-[10px] text-gray-500 mt-1">Ringkasan website Anda. Muncul di bawah judul pada pencarian Google. Usahakan max 160 karakter.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">SEO Meta Keywords</label>
                            <input type="text" name="seo_keywords" value="{{ $settings['seo_keywords'] ?? 'kerajinan, rima craft, anyaman, rotan, furniture' }}" class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                            <p class="text-[10px] text-gray-500 mt-1">Kata kunci yang berhubungan dengan bisnis Anda, pisahkan dengan koma (contoh: anyaman bambu, kursi rotan, kerajinan tangan).</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md shadow-sm transition-all text-sm">
                        Simpan Pengaturan SEO
                    </button>
                </div>
            </div>

            <!-- ==================== TAB INFO ==================== -->
            <div x-show="tab === 'info'" x-transition.opacity.duration.300ms style="display: none;" class="space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 shadow-sm p-4 md:p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Konten Halaman Informasi</h3>
                    <p class="text-xs text-gray-500 mb-6">Kelola konten untuk halaman Syarat & Ketentuan, Kebijakan Privasi, dan aturan Pengiriman dengan text editor.</p>
                    
                    <div class="space-y-8">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Syarat & Ketentuan (Terms and Conditions)</label>
                            <input id="page_terms" type="hidden" name="page_terms" value="{{ $settings['page_terms'] ?? '' }}">
                            <trix-editor input="page_terms" class="trix-content bg-white dark:bg-gray-900 text-gray-900 dark:text-white border-gray-300 dark:border-gray-700 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"></trix-editor>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Kebijakan Privasi (Privacy Policy)</label>
                            <input id="page_privacy" type="hidden" name="page_privacy" value="{{ $settings['page_privacy'] ?? '' }}">
                            <trix-editor input="page_privacy" class="trix-content bg-white dark:bg-gray-900 text-gray-900 dark:text-white border-gray-300 dark:border-gray-700 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"></trix-editor>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Pengiriman & Retur (Shipping & Returns)</label>
                            <input id="page_shipping" type="hidden" name="page_shipping" value="{{ $settings['page_shipping'] ?? '' }}">
                            <trix-editor input="page_shipping" class="trix-content bg-white dark:bg-gray-900 text-gray-900 dark:text-white border-gray-300 dark:border-gray-700 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"></trix-editor>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md shadow-sm transition-all text-sm">
                        Simpan Halaman Info
                    </button>
                </div>
            </div>

        </form>
    </div>
</x-layouts.app>
