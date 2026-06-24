<x-layouts.public>
    <div class="pt-32 pb-24 bg-gray-50 dark:bg-[#050505] min-h-screen transition-colors duration-500">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl md:text-5xl font-serif font-bold text-gray-900 dark:text-white mb-8 text-center">{{ $title }}</h1>
            
            <div class="bg-white dark:bg-[#0a0a0a] rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100 dark:border-white/5">
                <div class="prose prose-gray dark:prose-invert max-w-none prose-p:leading-relaxed prose-p:font-light">
                    @if(empty($content))
                        <p class="text-center text-gray-500 italic">Halaman ini sedang dalam pembaruan. Silakan kembali lagi nanti.</p>
                    @else
                        {!! $content !!}
                    @endif
                </div>
            </div>
            
            <div class="mt-12 text-center">
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-amber-600 dark:hover:text-amber-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>
