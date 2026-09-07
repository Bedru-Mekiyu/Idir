<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ይግቡ - እድር</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
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
</head>
<body class="leading-loose overflow-x-hidden bg-gray-50 text-gray-900 antialiased font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <a href="/" class="flex items-center gap-2">
                <x-logo class="h-10 mx-auto" />
            </a>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-4 sm:p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">እንኳን በደህና መጡ</h1>
                <p class="text-gray-500 text-sm">ወደ አካውንትዎ ለመግባት ስልክ ቁጥርዎን ያስገቡ</p>
            </div>

            @if($errors->any())
                <div class="mb-6 bg-gray-50 border border-gray-300 text-black rounded-xl p-4 text-sm font-medium">
                    {{ $errors->first() }}
                </div>
            @endif
            @if(session('status'))
                <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl p-4 text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('member.login.submit') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">ስልክ ቁጥር</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-gray-900 placeholder-gray-400"
                        placeholder="0911...">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">የይለፍ ቃል (Password)</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-gray-900 placeholder-gray-400"
                        placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-sm shadow-blue-600/20">
                    ይግቡ
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    አካውንት የሎትም? 
                    <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-700">አዲስ እድር ይመዝገቡ</a>
                </p>
            </div>
        </div>
        
        <div class="text-center mt-8 text-xs text-gray-400 font-medium">
            &copy; {{ date('Y') }} እድር አስተዳደር
        </div>
    </div>

</body>
</html>