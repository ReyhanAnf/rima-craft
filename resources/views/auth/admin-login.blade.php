<x-layouts.app>
    <!-- Admin Login - Compact & Flat Professional -->
    <div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-950">
        
        <div class="w-full max-w-sm px-4">
            <!-- Logo & Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl mb-3 shadow-lg shadow-amber-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Admin Panel</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">Rima Craft Management</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs border border-red-200 dark:border-red-800/50">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Login Form -->
                <form hx-post="{{ route('admin.login.store') }}" 
                      hx-target="body"
                      hx-swap="outerHTML"
                      class="space-y-4"
                      x-data="{ submitting: false }"
                      @submit="submitting = true"
                      @htmx:after-request="submitting = false">
                    @csrf
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none @error('email') border-red-500 @enderror"
                            placeholder="admin@rimacraft.com">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none @error('password') border-red-500 @enderror"
                            placeholder="••••••••">
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-3.5 h-3.5 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                            <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                        class="w-full flex justify-center items-center py-2.5 px-4 rounded-lg text-sm font-bold text-gray-950 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 shadow-lg shadow-amber-500/20 hover:shadow-amber-600/30 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        x-bind:disabled="submitting">
                        
                        <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="submitting ? 'Memproses...' : 'Login'"></span>
                    </button>
                </form>

                <!-- Back to Site -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('catalog.index') }}" class="flex items-center justify-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Website
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-4 text-center text-xs text-gray-400 dark:text-gray-500">
                &copy; {{ date('Y') }} Rima Craft
            </p>
        </div>

        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode" class="fixed top-4 right-4 p-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:scale-105 transition-all shadow-sm">
            <span x-show="!darkMode">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </span>
            <span x-show="darkMode" style="display: none;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </span>
        </button>
    </div>
</x-layouts.app>
