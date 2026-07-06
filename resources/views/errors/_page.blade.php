@php
    $status = (int) ($status ?? 500);

    $defaults = [
        400 => [
            'title' => 'Permintaan Tidak Valid',
            'message' => 'Data atau alamat yang dikirim tidak dapat diproses. Periksa kembali halaman yang Anda tuju.',
            'hint' => 'Coba ulangi dari halaman utama atau kembali ke halaman sebelumnya.',
        ],
        401 => [
            'title' => 'Perlu Login',
            'message' => 'Anda perlu masuk terlebih dahulu untuk membuka halaman ini.',
            'hint' => 'Gunakan akun yang sudah terdaftar, lalu lanjutkan aktivitas Anda.',
        ],
        403 => [
            'title' => 'Akses Ditolak',
            'message' => 'Akun Anda tidak memiliki izin untuk membuka halaman ini.',
            'hint' => 'Jika seharusnya Anda memiliki akses, hubungi admin Rima Craft.',
        ],
        404 => [
            'title' => 'Halaman Tidak Ditemukan',
            'message' => 'Halaman yang Anda cari mungkin sudah dipindahkan, dihapus, atau alamatnya tidak lengkap.',
            'hint' => 'Mulai lagi dari katalog atau gunakan navigasi yang tersedia.',
        ],
        405 => [
            'title' => 'Metode Tidak Didukung',
            'message' => 'Aksi yang dikirim tidak sesuai dengan cara akses halaman ini.',
            'hint' => 'Muat ulang halaman, lalu ulangi aksi dari tombol atau form yang tersedia.',
        ],
        408 => [
            'title' => 'Waktu Permintaan Habis',
            'message' => 'Koneksi terlalu lama menunggu respons dari server.',
            'hint' => 'Periksa koneksi internet Anda dan coba lagi sebentar lagi.',
        ],
        419 => [
            'title' => 'Sesi Berakhir',
            'message' => 'Sesi keamanan halaman ini sudah kedaluwarsa.',
            'hint' => 'Muat ulang halaman, lalu kirim ulang form jika diperlukan.',
        ],
        422 => [
            'title' => 'Data Belum Sesuai',
            'message' => 'Beberapa data yang dikirim belum memenuhi ketentuan sistem.',
            'hint' => 'Kembali ke form sebelumnya dan periksa isian yang diminta.',
        ],
        429 => [
            'title' => 'Terlalu Banyak Permintaan',
            'message' => 'Aktivitas dari perangkat ini terlalu cepat untuk diproses.',
            'hint' => 'Tunggu beberapa saat sebelum mencoba kembali.',
        ],
        500 => [
            'title' => 'Terjadi Kesalahan Server',
            'message' => 'Sistem sedang mengalami kendala saat memproses permintaan Anda.',
            'hint' => 'Tim kami dapat menelusuri masalah ini dari log server.',
        ],
        502 => [
            'title' => 'Gateway Bermasalah',
            'message' => 'Server menerima respons yang tidak valid dari layanan pendukung.',
            'hint' => 'Coba kembali beberapa saat lagi.',
        ],
        503 => [
            'title' => 'Layanan Sementara Tidak Tersedia',
            'message' => 'Rima Craft sedang dalam pemeliharaan atau menerima beban tinggi.',
            'hint' => 'Silakan kembali beberapa saat lagi.',
        ],
        504 => [
            'title' => 'Gateway Timeout',
            'message' => 'Layanan pendukung membutuhkan waktu terlalu lama untuk merespons.',
            'hint' => 'Coba ulangi setelah koneksi layanan kembali stabil.',
        ],
    ];

    $copy = array_merge($defaults[$status] ?? $defaults[500], [
        'title' => $title ?? ($defaults[$status]['title'] ?? $defaults[500]['title']),
        'message' => $message ?? ($defaults[$status]['message'] ?? $defaults[500]['message']),
        'hint' => $hint ?? ($defaults[$status]['hint'] ?? $defaults[500]['hint']),
    ]);

    $homeUrl = Route::has('catalog.index') ? route('catalog.index') : url('/');
    $loginUrl = Route::has('login') ? route('login') : null;
    $canGoToLogin = in_array($status, [401, 419], true) && $loginUrl;
@endphp

<x-layouts.public>
    {{-- Decorative ambient background --}}
    <section class="relative min-h-screen overflow-hidden bg-[#faf8f4] dark:bg-[#0c0a08]">

        {{-- Ghost status number as background texture --}}
        <div aria-hidden="true"
             class="pointer-events-none absolute inset-0 flex items-center justify-center select-none overflow-hidden">
            <span class="font-serif text-[28vw] font-black leading-none tracking-tighter text-stone-200/60 dark:text-stone-800/40">
                {{ $status }}
            </span>
        </div>

        {{-- Subtle grain texture overlay --}}
        <div aria-hidden="true"
             class="pointer-events-none absolute inset-0 opacity-[0.025] dark:opacity-[0.06]"
             style="background-image:url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');background-size:200px 200px;">
        </div>

        {{-- Amber glow spot --}}
        <div aria-hidden="true"
             class="pointer-events-none absolute -top-32 left-1/2 h-[500px] w-[700px] -translate-x-1/2 rounded-full bg-amber-400/10 blur-[120px] dark:bg-amber-500/8">
        </div>

        {{-- Content --}}
        <div class="relative z-10 flex min-h-screen flex-col items-center justify-center px-6 py-24 text-center">

            {{-- Eyebrow label --}}
            <div class="mb-8 inline-flex items-center gap-2.5 rounded-full border border-amber-300/60 bg-amber-50/80 px-4 py-1.5 backdrop-blur-sm dark:border-amber-500/20 dark:bg-amber-500/8">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span>
                <span class="font-mono text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-400">
                    HTTP {{ $status }}
                </span>
            </div>

            {{-- Title --}}
            <h1 class="font-serif text-4xl font-bold leading-[1.15] tracking-tight text-stone-900 dark:text-stone-50 sm:text-5xl md:text-6xl">
                {{ $copy['title'] }}
            </h1>

            {{-- Divider --}}
            <div class="mx-auto mt-8 flex items-center gap-3">
                <div class="h-px w-12 bg-gradient-to-r from-transparent to-amber-400 dark:to-amber-500"></div>
                <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                <div class="h-px w-12 bg-gradient-to-l from-transparent to-amber-400 dark:to-amber-500"></div>
            </div>

            {{-- Message --}}
            <p class="mx-auto mt-7 max-w-xl text-base leading-relaxed text-stone-500 dark:text-stone-400 sm:text-lg">
                {{ $copy['message'] }}
            </p>

            {{-- Hint card --}}
            <div class="mx-auto mt-7 max-w-md rounded-2xl border border-stone-200/80 bg-white/70 px-6 py-4 text-sm leading-relaxed text-stone-500 shadow-sm backdrop-blur-md dark:border-white/8 dark:bg-white/4 dark:text-stone-400">
                <span class="mr-1.5 text-amber-500">✦</span>{{ $copy['hint'] }}
            </div>

            {{-- Actions --}}
            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $homeUrl }}"
                   class="group inline-flex items-center gap-2 rounded-full bg-amber-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:bg-amber-600 hover:shadow-amber-600/30 dark:bg-amber-500 dark:hover:bg-amber-600">
                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h14V10"/>
                    </svg>
                    Ke Beranda
                </a>

                @if ($canGoToLogin)
                    <a href="{{ $loginUrl }}"
                       class="group inline-flex items-center gap-2 rounded-full border border-stone-300/70 bg-white/80 px-6 py-3 text-sm font-semibold text-stone-700 backdrop-blur-sm transition-all duration-200 hover:border-amber-400 hover:text-amber-700 dark:border-white/10 dark:bg-white/5 dark:text-stone-300 dark:hover:border-amber-500 dark:hover:text-amber-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H3m12 0-4-4m4 4-4 4m4-11h4a2 2 0 012 2v10a2 2 0 01-2 2h-4"/>
                        </svg>
                        Masuk
                    </a>
                @else
                    <button type="button"
                            onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ $homeUrl }}'"
                            class="group inline-flex items-center gap-2 rounded-full border border-stone-300/70 bg-white/80 px-6 py-3 text-sm font-semibold text-stone-700 backdrop-blur-sm transition-all duration-200 hover:border-amber-400 hover:text-amber-700 dark:border-white/10 dark:bg-white/5 dark:text-stone-300 dark:hover:border-amber-500 dark:hover:text-amber-400">
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0 7-7m-7 7h18"/>
                        </svg>
                        Kembali
                    </button>
                @endif
            </div>

            {{-- Footer note --}}
            <p class="mt-14 font-mono text-[10px] uppercase tracking-[0.18em] text-stone-400/70 dark:text-stone-600">
                Rima Craft &nbsp;·&nbsp; Kode Error {{ $status }}
            </p>

        </div>
    </section>
</x-layouts.public>
