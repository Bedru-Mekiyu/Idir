<!DOCTYPE html>
<html lang="am" class="h-full bg-slate-50 antialiased selection:bg-emerald-500 selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ስልክ ቁጥርዎን ያረጋግጡ (Verify Phone) — እድር ፕላትፎርም</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="min-h-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-slate-50">
    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-700 rounded-3xl flex items-center justify-center mx-auto mb-3 shadow-inner text-2xl">
                📲
            </div>
            <h1 class="text-2xl font-black text-slate-900">ስልክ ቁጥርዎን ያረጋግጡ</h1>
            <p class="text-xs text-slate-500 mt-1.5">
                የማረጋገጫ ኮድ ወደ <strong class="text-slate-800 font-bold">{{ $user->phone }}</strong> ተልኳል
            </p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 p-6 sm:p-8 space-y-6">
            @if (session('status'))
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-xs font-semibold text-emerald-800 flex items-center gap-2">
                    <span>✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3.5 rounded-2xl bg-red-50 border border-red-100 text-xs font-semibold text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <span>⚠️</span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('phone.verify.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="code" class="block text-xs font-bold text-slate-700 mb-1.5 text-center">
                        ባለ 6 አሃዝ የማረጋገጫ ኮድ (6-digit OTP Code)
                    </label>
                    <input type="text" id="code" name="code" required autofocus maxlength="6"
                        placeholder="123456"
                        class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-center text-2xl font-black tracking-widest text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    <p class="text-[11px] text-slate-400 text-center mt-2">
                        በአካባቢ ሙከራ (Local test)፡ <code class="bg-slate-100 px-2 py-0.5 rounded text-slate-600 font-bold">123456</code> መጠቀም ይችላሉ
                    </p>
                </div>

                <button type="submit"
                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-900/20 transition transform active:scale-98">
                    አረጋግጥና እድር ፍጠር (Verify & Continue) &rarr;
                </button>
            </form>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">ኮዱ አልደረሰዎትም?</span>
                <form method="POST" action="{{ route('phone.verify.resend') }}">
                    @csrf
                    <button type="submit" class="font-bold text-emerald-600 hover:underline">
                        ኮድ በድጋሚ ላክ (Resend)
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
