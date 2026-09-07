<!DOCTYPE html>
<html lang="am" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የማኔጀርነት ፈቃድ ማመልከቻ — እድር ፕላትፎርም</title>
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
        <div class="max-w-xl w-full my-6">

            <!-- Brand Header & Status Strip -->
            <div class="text-center mb-6">
                <a href="/"><x-logo class="h-10" /></a>
                
                <!-- Step Indicator -->
                <div class="flex items-center justify-center gap-2 mt-4">
                    
                    <span class="inline-flex items-center gap-1 text-xs text-gray-500 font-medium font-sans">
                        <span>✓ {{ $user->phone }}</span>
                    </span>
                </div>
            </div>

            <!-- Flash Status Messages -->
            @if (session('status'))
                <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-xs font-semibold text-blue-800 flex items-start gap-3 shadow-none">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 rounded-2xl bg-gray-100 border border-gray-300 text-xs font-semibold text-black flex items-start gap-3 shadow-none">
                    <svg class="w-4 h-4 text-black shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>{{ session('warning') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-gray-50 border border-gray-300 text-xs text-black space-y-1 shadow-none">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2 font-medium">
                            <svg class="w-4 h-4 text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- STATE 1: ACCESS GRANTED --}}
            @if ($user->canCreateIdir())
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 sm:p-8 space-y-6 text-center">
                    <div class="w-16 h-16 bg-blue-50 text-blue-700 rounded-2xl flex items-center justify-center mx-auto text-2xl shadow-none border border-blue-200">
                        ✓
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-blue-700 uppercase tracking-wider mb-2">
                            ፈቃድ ተሰጥቷል
                        </span>
                        <h2 class="text-2xl font-bold text-gray-900 mt-1">የማኔጀርነት ፈቃድ አግኝተዋል!</h2>
                        <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                            የፕላትፎርም ባለቤቱ የእድር ማኔጀርነት (ሰብሳቢነት) ፈቃድ ሰጥተዎታል። አሁን አዲሱን እድርዎን በሲስተሙ ውስጥ መመዝገብ ይችላሉ።
                        </p>
                    </div>

                    <div class="pt-4">
                        <a href="/committee/new"
                            class="inline-flex items-center justify-center w-full py-4 px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold text-base rounded-xl shadow-sm transition transform hover:-translate-y-0.5 active:scale-95 gap-2">
                            <span>እድር መመዝገቢያውን ክፈት</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>

            {{-- STATE 2: REQUEST PENDING REVIEW --}}
            @elseif ($latestRequest && $latestRequest->isPending() && !$reapply)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-300 p-6 sm:p-8 space-y-6">
                    <div class="text-center">
                        <div class="w-14 h-14 bg-gray-100 text-black rounded-2xl flex items-center justify-center mx-auto text-2xl shadow-none border border-gray-300 mb-3">
                            ⏳
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-black uppercase tracking-wider mb-2">
                            በግምገማ ላይ
                        </span>
                        <h2 class="text-2xl font-bold text-gray-900 mt-1">ጥያቄዎ በግምገማ ላይ ነው</h2>
                        <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                            የማኔጀርነት ፈቃድ ጥያቄዎ ለፕላትፎርም ባለቤቱ ቀርቧል። ባለቤቱ ገምግመው ሲያጸድቁ በኤስኤምኤስ (<span class="font-sans font-semibold text-gray-900">{{ $user->phone }}</span>) ይደርስዎታል።
                        </p>
                    </div>

                    <!-- Request Details Card -->
                    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-5 space-y-3 text-xs">
                        <h3 class="font-bold text-gray-900 text-sm border-b border-gray-200 pb-2 flex items-center justify-between">
                            <span>የቀረበው ጥያቄ ዝርዝር</span>
                            <span class="text-gray-500 font-normal font-sans">{{ $latestRequest->created_at->format('M d, Y H:i') }}</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <span class="text-gray-500 block">የታሰበው እድር ስም</span>
                                <span class="font-bold text-gray-900 text-sm">{{ $latestRequest->idir_name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">የአባልነት መሠረት</span>
                                <span class="font-semibold text-gray-700">{{ $latestRequest->membership_basis ?? 'አልተጠቀሰም' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">ክልል / ከተማ</span>
                                <span class="font-semibold text-gray-700">{{ $latestRequest->region ?? 'አልተጠቀሰም' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">ክፍለ ከተማ / ወረዳ</span>
                                <span class="font-semibold text-gray-700">{{ $latestRequest->sub_city ?? 'አልተጠቀሰም' }}</span>
                            </div>
                        </div>
                        @if ($latestRequest->purpose)
                            <div class="pt-2 border-t border-gray-200">
                                <span class="text-gray-500 block">የማመልከቻው ዓላማ / መግለጫ</span>
                                <p class="font-medium text-gray-700 mt-1 italic">"{{ $latestRequest->purpose }}"</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                        <a href="{{ route('access-request') }}"
                            class="w-full sm:flex-1 py-3 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>ሁኔታውን አድስ</span>
                        </a>
                    </div>

                    <div class="text-center text-[11px] text-gray-500 pt-2 border-t border-gray-100">
                        ፈቃድ ሳይሰጥዎት በቀጥታ ወደ እድር መመዝገቢያው መግባት አይፈቀድም።
                    </div>
                </div>

            {{-- STATE 3: REQUEST DENIED --}}
            @elseif ($latestRequest && $latestRequest->isDenied() && !$reapply)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-300 p-6 sm:p-8 space-y-6">
                    <div class="text-center">
                        <div class="w-14 h-14 bg-gray-50 text-black rounded-2xl flex items-center justify-center mx-auto text-2xl shadow-none border border-gray-300 mb-3">
                            ✕
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-black uppercase tracking-wider mb-2">
                            ውድቅ ተደርጓል
                        </span>
                        <h2 class="text-2xl font-bold text-gray-900 mt-1">ጥያቄዎ ውድቅ ተደርጓል</h2>
                        <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                            ያቀረቡት የማኔጀርነት ፈቃድ ጥያቄ በፕላትፎርም ባለቤቱ ውድቅ ተደርጓል።
                        </p>
                    </div>

                    <!-- Denial Reason Banner -->
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-300 space-y-1">
                        <div class="text-xs font-bold text-black flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>ምክንያት:</span>
                        </div>
                        <p class="text-sm text-black font-semibold pl-6">
                            {{ $latestRequest->denial_reason ?? 'ምክንያት አልተገለጸም።' }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-4 text-xs text-gray-600 leading-relaxed border border-gray-200">
                        የቀረበውን ምክንያት ከግምት በማስገባት የተሟላ መረጃ የያዘ አዲስ ማመልከቻ በድጋሚ ማቅረብ ይችላሉ።
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('access-request', ['reapply' => 1]) }}"
                            class="inline-flex items-center justify-center w-full py-3.5 px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-sm transition transform active:scale-95 gap-2">
                            <span>አዲስ ጥያቄ አቅርብ</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>

            {{-- STATE 4: SUBMISSION FORM --}}
            @else
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 sm:p-8 space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">የማኔጀርነት ፈቃድ ማመልከቻ</h2>
                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                            እድር ለመመሥረት እና ለማስተዳደር የፕላትፎርም ባለቤቱ ፈቃድ ያስፈልጋል። እባክዎ ሊመሠርቱት ስላሰቡት እድር አጭር መረጃ ያስገቡ።
                        </p>
                    </div>

                    <form method="POST" action="{{ route('access-request.store') }}" class="space-y-4">
                        @csrf

                        <!-- Proposed Idir Name -->
                        <div>
                            <label for="idir_name" class="block text-xs font-bold text-gray-700 mb-1.5">
                                ሊመሠረት የታሰበው እድር ስም *
                            </label>
                            <input type="text" id="idir_name" name="idir_name" value="{{ old('idir_name') }}" required autofocus
                                placeholder="ለምሳሌ፡ አዲስ ተስፋ እድር"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                        </div>

                        <!-- Membership Basis -->
                        <div>
                            <label for="membership_basis" class="block text-xs font-bold text-gray-700 mb-1.5">
                                የአባልነት መሠረት
                            </label>
                            <input type="text" id="membership_basis" name="membership_basis" value="{{ old('membership_basis') }}"
                                placeholder="ለምሳሌ፡ የሠፈር፣ የዕድር፣ የቤተሰብ፣ የሥራ ባልደረቦች..."
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                        </div>

                        <!-- Region & Sub-city -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="region" class="block text-xs font-bold text-gray-700 mb-1.5">
                                    ክልል / ከተማ
                                </label>
                                <input type="text" id="region" name="region" value="{{ old('region') }}"
                                    placeholder="አዲስ አበባ፣ አማራ..."
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                            </div>
                            <div>
                                <label for="sub_city" class="block text-xs font-bold text-gray-700 mb-1.5">
                                    ክፍለ ከተማ / ወረዳ
                                </label>
                                <input type="text" id="sub_city" name="sub_city" value="{{ old('sub_city') }}"
                                    placeholder="ቦሌ፣ የካ፣ ወረዳ 03..."
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                            </div>
                        </div>

                        <!-- Purpose / Description -->
                        <div>
                            <label for="purpose" class="block text-xs font-bold text-gray-700 mb-1.5">
                                የማመልከቻው ዓላማ / ማብራሪያ
                            </label>
                            <textarea id="purpose" name="purpose" rows="3"
                                placeholder="እድሩን ለማቋቋም ያሰቡበትን ምክንያት እና የታሰበውን የአባላት ሁኔታ በአጭሩ ይግለጹ..."
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">{{ old('purpose') }}</textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-sm transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span>ጥያቄ አስገባ</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>

                        @if ($reapply)
                            <div class="text-center pt-2">
                                <a href="{{ route('access-request') }}" class="text-xs text-gray-500 hover:text-gray-800 underline">
                                    ተመለስ
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            @endif

            <!-- Footer / Logout -->
            <div class="mt-6 flex items-center justify-between text-xs text-gray-500 px-2">
                <span>እድር አስተዳደር ፕላትፎርም &copy; {{ date('Y') }}</span>
                <form method="POST" action="{{ route('member.logout') }}">
                    @csrf
                    <button type="submit" class="hover:text-gray-900 font-medium hover:underline">
                        ውጣ
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Minimal Footer -->
    <footer class="py-6 text-center text-xs text-gray-400 border-t border-gray-200 bg-white">
        &copy; {{ date('Y') }} እድር ፕላትፎርም። መብቱ በሕግ የተጠበቀ ነው።
    </footer>
</body>
</html>