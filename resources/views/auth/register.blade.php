<!DOCTYPE html>
<html lang="am" class="h-full bg-slate-50 antialiased selection:bg-emerald-500 selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>አዲስ መለያ ይክፈቱ (Sign Up) — እድር ፕላትፎርም</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="min-h-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-slate-50">
    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-tr from-emerald-600 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-900/20 text-white font-black text-2xl">
                    እ
                </div>
                <span class="text-2xl font-black text-slate-900 tracking-tight">እድር (Idir)</span>
            </a>
            <h1 class="text-xl font-black text-slate-900 mt-4">አዲስ መለያ ይክፈቱ</h1>
            <p class="text-xs text-slate-500 mt-1">የእርስዎን እድር ለመመዝገብ መጀመሪያ የግል መለያዎን ይክፈቱ</p>
        </div>

        <!-- Registration Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 p-6 sm:p-8">
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <span>⚠️</span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">ሙሉ ስም (Full Name) *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="ለምሳሌ፡ አልማዝ ደረጀ"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                </div>

                <!-- Ethiopian Phone Number -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">ስልክ ቁጥር (Ethiopian Phone) *</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                        placeholder="0911223344 ወይም 0711223344"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    <p class="text-[11px] text-slate-400 mt-1">የማረጋገጫ ኮድ (SMS OTP) ወደዚህ ስልክ ይላካል</p>
                </div>

                <!-- Email (Optional) -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1">ኢሜይል (Email - Optional)</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        placeholder="almaz@example.com"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1">የይለፍ ቃል (Password) *</label>
                    <input type="password" id="password" name="password" required
                        placeholder="ቢያንስ 8 ፊደላት/ቁጥሮች"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">የይለፍ ቃል ማረጋገጫ *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        placeholder="የይለፍ ቃሉን በድጋሚ ያስገቡ"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-900/20 transition transform active:scale-98">
                        ይመዝገቡና ይቀጥሉ (Sign Up) &rarr;
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                መለያ አለዎት? <a href="/committee/login" class="font-bold text-emerald-600 hover:underline">ይግቡ (Log In)</a>
            </div>
        </div>
    </div>
</body>
</html>
