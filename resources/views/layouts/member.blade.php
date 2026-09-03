<!DOCTYPE html>
<html lang="am" dir="ltr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'እድር - የኢትዮጵያ እድር አስተዳደር ዲጂታል መድረክ')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        gold: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts: Noto Sans Ethiopic & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Ethiopic', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-slate-800">
    <!-- Top Navigation Bar -->
    <header class="no-print sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo & Idir Context -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('member.dashboard') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white flex items-center justify-center font-black text-xl shadow-md shadow-brand-700/20 group-hover:scale-105 transition-transform">
                            እ
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-lg text-slate-900 tracking-tight">እድር</span>
                                <span class="text-xs bg-brand-100 text-brand-800 font-semibold px-2 py-0.5 rounded-full">ዲጂታል</span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium truncate max-w-[200px] sm:max-w-xs">
                                {{ $member->idir->name ?? 'የኢትዮጵያ እድር' }}
                            </p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    @auth
                        <a href="{{ route('member.dashboard') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('member.dashboard') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            ዳሽቦርድ
                        </a>
                        <a href="{{ route('member.claims.create') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('member.claims.create') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            የክፍያ ጥያቄ
                        </a>
                        <a href="{{ route('member.lookup') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition">
                            የአባል ፍለጋ
                        </a>
                    @else
                        <a href="{{ route('member.lookup') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition">
                            ፈጣን ፍለጋ
                        </a>
                        <a href="{{ route('member.login') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 transition">
                            መግቢያ (Login)
                        </a>
                    @endauth
                </nav>

                <!-- Actions & Profile -->
                <div class="flex items-center gap-3">
                    <a href="/committee" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-brand-700 bg-slate-100 hover:bg-slate-200/70 px-3 py-1.5 rounded-lg transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        የኮሚቴ ዳሽቦርድ
                    </a>

                    @auth
                        <form method="POST" action="{{ route('member.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-slate-500 hover:text-red-600 font-medium px-2.5 py-1.5 rounded-lg transition">
                                ውጣ
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @if(session('success'))
            <div class="no-print mb-6 p-4 bg-brand-50 border border-brand-200 text-brand-900 rounded-xl shadow-xs flex items-center gap-3">
                <svg class="w-5 h-5 text-brand-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="no-print mb-6 p-4 bg-red-50 border border-red-200 text-red-900 rounded-xl shadow-xs">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-bold text-sm">እባክዎ የሚከተሉትን ስህተቶች ያርሙ፦</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 text-red-700 ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="no-print md:hidden sticky bottom-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-lg px-6 py-2 flex items-center justify-around">
        <a href="{{ route('member.dashboard') }}" class="flex flex-col items-center gap-1 text-xs font-semibold {{ request()->routeIs('member.dashboard') ? 'text-brand-700' : 'text-slate-500' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            ዳሽቦርድ
        </a>
        <a href="{{ route('member.claims.create') }}" class="flex flex-col items-center gap-1 text-xs font-semibold {{ request()->routeIs('member.claims.create') ? 'text-brand-700' : 'text-slate-500' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            ጥያቄ
        </a>
        <a href="{{ route('member.lookup') }}" class="flex flex-col items-center gap-1 text-xs font-semibold {{ request()->routeIs('member.lookup') ? 'text-brand-700' : 'text-slate-500' }}">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            ፍለጋ
        </a>
    </nav>

    <!-- Footer -->
    <footer class="no-print bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 space-y-2">
            <div class="flex items-center justify-center gap-4 text-slate-400">
                <span>የኢትዮጵያ እድር አስተዳደር ዲጂታል መድረክ</span>
                <span>&bull;</span>
                <span>ኢትዮጵያ ብቻ (ETB)</span>
            </div>
            <p>&copy; {{ date('Y') }} እድር (Idir Management Platform). መብቱ በሕግ የተጠበቀ ነው።</p>
        </div>
    </footer>
</body>
</html>
