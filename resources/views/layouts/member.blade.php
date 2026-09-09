<!DOCTYPE html>
<html lang="am" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'እድር' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Noto Sans Ethiopic"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Noto Sans Ethiopic', sans-serif; }
    </style>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-x-hidden bg-gray-50 text-gray-900 antialiased leading-loose min-h-screen flex flex-col">

    <!-- App Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 sm:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 mr-4">
                        <x-logo class="h-8" />
                    </a>
                    
                    <div class="hidden sm:flex sm:space-x-4">
                        <a href="{{ route('member.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('member.dashboard') ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                            ዋና ገጽ
                        </a>
                        <a href="{{ route('member.pay') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('member.pay*') || request()->routeIs('member.payment.*') ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                            ክፍያ
                        </a>
                        <a href="{{ route('member.claims.create') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('member.claims.*') ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                            ካሳ ጠይቅ
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth()->user()->name }}</span>
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="Profile" class="w-9 h-9 rounded-full object-cover border border-gray-200 shadow-sm">
                        <a href="{{ route('member.profile') }}" class="text-xs text-blue-600 hover:text-blue-800 font-bold ml-2">ፕሮፋይል</a>
                        <form method="POST" action="{{ route('member.logout') }}" class="ml-2 border-l border-gray-300 pl-2">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 font-medium">ውጣ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="flex-grow py-8 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 sm:px-8">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 sm:px-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} እድር ፕላትፎርም
        </div>
    </footer>

    @livewireScripts
</body>
</html>