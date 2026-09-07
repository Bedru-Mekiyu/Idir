<!DOCTYPE html>
<html lang="am" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ስልክ ቁጥርዎን ያረጋግጡ — እድር ፕላትፎርም</title>
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



                <h1 class="text-xl font-bold text-gray-900 mt-2">ስልክ ቁጥርዎን ያረጋግጡ</h1>
                <p class="text-xs text-gray-500 mt-1">
                    የማረጋገጫ ኮድ ወደ <strong class="text-gray-900 font-bold font-sans">{{ $user->phone }}</strong> ተልኳል
                </p>
            </div>

            <!-- OTP Card -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 space-y-6">
                @if (session('status'))
                    <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-xs font-semibold text-blue-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-300 text-xs font-semibold text-black space-y-1">
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

                <form method="POST" action="{{ route('phone.verify.submit') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="code" class="block text-xs font-bold text-gray-700 mb-2 text-center uppercase tracking-wider">
                            ባለ 6 አሃዝ የማረጋገጫ ኮድ
                        </label>
                        <input type="text" id="code" name="code" required autofocus maxlength="6"
                            placeholder="123456"
                            class="w-full px-4 py-3.5 rounded-xl bg-gray-50 border border-gray-200 text-center text-3xl font-extrabold tracking-[0.3em] font-sans text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                        <p class="text-[11px] text-gray-500 text-center mt-2.5">
                            ለሙከራ: <code class="bg-gray-100 px-2 py-0.5 rounded text-gray-700 font-bold font-sans">123456</code> መጠቀም ይችላሉ
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-sm transition transform active:scale-95 flex items-center justify-center gap-2">
                        <span>አረጋግጥና ቀጥል</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-gray-500">ኮዱ አልደረሰዎትም?</span>
                    <form method="POST" action="{{ route('phone.verify.resend') }}">
                        @csrf
                        <button type="submit" class="font-bold text-blue-600 hover:underline">
                            ኮድ በድጋሚ ላክ
                        </button>
                    </form>
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