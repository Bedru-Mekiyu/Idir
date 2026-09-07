<!DOCTYPE html>
<html lang="am" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>አዲስ መለያ ይክፈቱ — እድር ፕላትፎርም</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    
    <!-- Google Fonts: Noto Sans Ethiopic & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Noto Sans Ethiopic"', '"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .tibeb-ribbon { background: linear-gradient(90deg, #1d4ed8 0%, #000000 50%, #1d4ed8 100%); height: 4px; }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased font-sans min-h-screen flex flex-col justify-between">
    <!-- Top Accent Bar -->
    <div class="tibeb-ribbon w-full"></div>

    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="max-w-md w-full my-6">
            <!-- Brand Logo & Header -->
            <div class="text-center mb-8">
                <a href="/"><x-logo class="h-10 mx-auto" /></a>
                


                <h1 class="text-xl font-bold text-gray-900 mt-2">አዲስ መለያ ይክፈቱ</h1>
                <p class="text-xs text-gray-500 mt-1">የእርስዎን እድር ለመመዝገብ መጀመሪያ የግል መለያዎን ያዘጋጁ</p>
            </div>

            <!-- Registration Card -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-gray-50 border border-gray-300 text-xs text-black space-y-1">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
                    @csrf

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-gray-700 mb-1.5">ሙሉ ስም *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                            placeholder="ለምሳሌ፡ አልማዝ ደረጀ"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                    </div>

                    <!-- Ethiopian Phone Number -->
                    <div>
                        <label for="phone" class="block text-xs font-bold text-gray-700 mb-1.5">ስልክ ቁጥር *</label>
                        <div class="relative">
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                                placeholder="0911223344 ወይም 0711223344"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition font-sans">
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">የማረጋገጫ ኮድ ወደዚህ ስልክ ቁጥር ይላካል</p>
                    </div>

                    <!-- Email (Optional) -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-gray-700 mb-1.5">ኢሜይል (አስፈላጊ ካልሆነ)</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="almaz@example.com"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition font-sans">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-700 mb-1.5">የይለፍ ቃል *</label>
                        <input type="password" id="password" name="password" required
                            placeholder="ቢያንስ 8 ፊደላት/ቁጥሮች"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-gray-700 mb-1.5">የይለፍ ቃል ማረጋገጫ *</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            placeholder="የይለፍ ቃሉን በድጋሚ ያስገቡ"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-sm transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span>ይመዝገቡና ይቀጥሉ</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-4 border-t border-gray-100 text-center text-xs text-gray-500">
                    መለያ አለዎት? <a href="/committee/login" class="font-bold text-blue-600 hover:underline">ይግቡ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Minimal Footer -->
    <footer class="py-6 text-center text-xs text-gray-400 border-t border-gray-200 bg-white">
        &copy; {{ date('Y') }} እድር ፕላትፎርም። መብቱ በሕግ የተጠበቀ ነው።
    </footer>
</body>
</html>