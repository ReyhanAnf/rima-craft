<x-layouts.app>
    <div class="flex items-center justify-center min-h-[85vh] md:min-h-full">
        <button @click="darkMode = !darkMode" class="absolute top-6 right-6 p-3 rounded-full bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-800 text-gray-600 dark:text-gray-300 hover:scale-105 transition-transform z-50">
            <span x-show="!darkMode">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </span>
            <span x-show="darkMode" style="display: none;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </span>
        </button>

        <div class="w-full max-w-md glass-card p-4 rounded-md relative overflow-hidden">
            <!-- Dekorasi Gradient Lingkaran -->
            
            

            <div class="text-center mb-10 relative z-10">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-400 to-primary-600 rounded-md flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-extrabold tracking-tight">Selamat Datang</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Masuk untuk melanjutkan ke sistem</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-md bg-red-50/80 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm border border-red-100 dark:border-red-500/20 relative z-10">
                    <ul class="list-disc pl-5 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form hx-post="{{ route('login.store') }}" 
                  hx-target="body"
                  hx-swap="outerHTML"
                  class="space-y-6 relative z-10"
                  x-data="{ submitting: false }"
                  @submit="submitting = true"
                  @htmx:after-request="submitting = false">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-semibold mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3.5 rounded-md border border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-900/50 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/20 transition-all outline-none @error('email') border-red-500 @enderror"
                        placeholder="admin@rimacraft.com">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-semibold">Password</label>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3.5 rounded-md border border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-900/50 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/20 transition-all outline-none @error('password') border-red-500 @enderror"
                        placeholder="********">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500 transition-colors cursor-pointer dark:bg-gray-800 dark:border-gray-600">
                        <span class="ml-3 text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors">Ingat sesi saya</span>
                    </label>
                </div>

                <button type="submit" 
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 focus:outline-none focus:ring-4 focus:ring-primary-500/30 transition-all transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none"
                    x-bind:disabled="submitting">
                    
                    <span x-show="!submitting">Login ke Dashboard</span>
                    <span x-show="submitting" class="flex items-center" style="display: none;">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Otentikasi...
                    </span>
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>

