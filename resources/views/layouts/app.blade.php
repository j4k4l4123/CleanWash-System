<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Manajemen Laundry') - Laundry Kelompok 2</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="alternate icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <!-- Tailwind CSS CDN for instant zero-config rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <span class="font-bold text-slate-900 tracking-tight text-lg leading-tight">CleanWash</span>
                    </a>
                </div>

                <!-- Desktop Nav Links -->
                @auth
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('orders.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('orders.*') ? 'text-brand-700 bg-brand-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Order Cucian
                        </a>
                        <a href="{{ route('customers.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('customers.*') ? 'text-brand-700 bg-brand-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Pelanggan
                        </a>
                    </nav>
                @endauth

                <!-- Right Actions -->
                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        <a href="{{ route('orders.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Order Baru</span>
                        </a>

                        <!-- User Profile & Logout -->
                        <div class="hidden lg:flex items-center gap-2.5 pl-2.5 border-l border-slate-200 text-xs">
                            <div class="text-right">
                                <div class="font-bold text-slate-800 leading-tight">{{ Auth::user()->name }}</div>
                                <div class="text-[10px] text-slate-500 leading-none flex items-center justify-end gap-1 mt-0.5">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full {{ Auth::user()->isAdmin() ? 'bg-purple-500' : 'bg-brand-500' }}"></span>
                                    <span>{{ Auth::user()->role_label }}</span>
                                </div>
                            </div>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Keluar dari sistem" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition shadow-xs">
                            <span>Masuk</span>
                        </a>
                    @endauth

                    <!-- Mobile menu button -->
                    <button type="button" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav -->
            <div id="mobileMenu" class="hidden md:hidden py-3 border-t border-slate-100 space-y-1">
                @auth
                    <div class="px-3 py-2 bg-slate-50 rounded-lg flex items-center justify-between mb-2">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">{{ Auth::user()->name }}</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[10px] px-1.5 py-0.5 rounded-sm font-semibold border {{ Auth::user()->role_badge_class }}">{{ Auth::user()->role_label }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ Auth::user()->email }}</span>
                            </div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Keluar</button>
                        </form>
                    </div>
                    <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('orders.*') ? 'text-brand-700 bg-brand-50' : 'text-slate-600' }}">Order Cucian</a>
                    <a href="{{ route('customers.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('customers.*') ? 'text-brand-700 bg-brand-50' : 'text-slate-600' }}">Pelanggan</a>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-brand-600">Masuk Petugas</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-sm font-bold ml-4">✕</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-sm font-bold ml-4">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 shadow-xs">
                <div class="flex items-center gap-2 font-semibold text-sm mb-2 text-amber-800">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>Harap periksa kesalahan input berikut:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 text-amber-800">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto text-center text-xs text-slate-500 no-print">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; {{ date('Y') }} Laundry Kelompok 2. Sistem Manajemen Laundry.</span>
            <div class="flex items-center gap-4 text-slate-400">
                <span>CleanWash</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
