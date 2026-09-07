<!DOCTYPE html>
<html lang="am" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>እድር</title>
    
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
        .hero-pattern {
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .mockup-shadow {
            box-shadow: 0 20px 40px -15px rgba(67, 56, 202, 0.15), 0 0 0 1px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="overflow-x-hidden bg-white text-gray-900 antialiased leading-loose min-h-screen flex flex-col">

    <!-- Navigation -->
    <nav class="w-full bg-gray-50/80 backdrop-blur-md border-b border-gray-200/60 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 sm:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <x-logo class="h-8" />
                </div>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                    <a href="#features" class="text-gray-600 hover:text-gray-900 transition">ዋና ዋና ገጽታዎች</a>
                    
                    
                    <div class="w-px h-4 bg-gray-300"></div>
                    
                    @auth
                        <a href="{{ route('member.dashboard') }}" class="text-blue-600 font-bold hover:text-blue-700 transition">ወደ መድረክ ይግቡ &rarr;</a>
                    @else
                        <a href="{{ route('member.login') }}" class="text-gray-600 hover:text-gray-900 font-bold transition">ይግቡ</a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-full bg-gray-900 text-white hover:bg-gray-800 font-bold transition shadow-sm">እድር ይመዝገቡ</a>
                    @endauth
                </div>

                <!-- Mobile Nav Toggle -->
                <div class="md:hidden flex items-center">
                    <a href="{{ route('member.login') }}" class="text-sm font-bold text-gray-900 mr-4">ይግቡ</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="relative pt-16 pb-24 lg:pt-24 lg:pb-32 overflow-hidden">
            <div class="absolute inset-0 hero-pattern opacity-50 pointer-events-none"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 sm:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                    
                    <!-- Left: Text Content -->
                    <div class="max-w-2xl mx-auto lg:mx-0 text-center lg:text-left">
                        

                        <h1 class="text-3xl sm:text-3xl lg:text-3xl font-bold tracking-tight text-gray-900 leading-[1.15] mb-6">
                            የእድር ማህበራትን <br class="hidden sm:block" />
                            <span class="text-blue-600">በዲጂታል ዘመን</span> ያስተዳድሩ
                        </h1>
                        
                        <p class="text-lg text-gray-600 leading-loose mb-8 max-w-xl mx-auto lg:mx-0">
                            የአባላት መዋጮን፣ የካሳ ክፍያዎችን እና የፋይናንስ ሪፖርቶችን በአንድ ዘመናዊ መድረክ ላይ በግልጽነት እና በቅልጥፍና ይምሩ።
                        </p>
                        
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                            <a href="{{ route('register') }}" class="w-full sm:w-auto px-4 sm:px-8 py-3.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 font-bold transition shadow-sm shadow-blue-600/30 flex items-center justify-center gap-2">
                                እድር ይመዝገቡ
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                            <a href="#features" class="w-full sm:w-auto px-4 sm:px-8 py-3.5 rounded-full bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 font-bold transition flex items-center justify-center">
                                ተጨማሪ ይመልከቱ
                            </a>
                        </div>
                    </div>

                    <!-- Right: Stylized Dashboard Mockup -->
                    <div class="relative w-full max-w-lg mx-auto lg:max-w-none lg:mr-0">
                        <!-- Decorative background blob -->
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-[120%] bg-blue-100/50 rounded-full blur-3xl -z-10"></div>
                        
                        <!-- The Mockup Frame -->
                        <div class="mockup-shadow bg-white rounded-2xl overflow-hidden border border-gray-200/80 transform lg:-rotate-2 transition-transform hover:rotate-0 duration-500">
                            
                            <!-- Mockup Header -->
                            <div class="bg-gray-50 border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center border border-gray-200">
        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">አበበ ተሰማ</div>
                                        <div class="text-xs text-gray-500">መደበኛ አባል</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200 shadow-sm">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
        ተከፍሏል
    </div>
                            </div>

                            <!-- Mockup Body -->
                            <div class="p-6 space-y-6">
                                <!-- Balance Card -->
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -z-10"></div>
                                    <div class="text-xs font-bold text-gray-500 mb-1">አጠቃላይ መዋጮ</div>
                                    <div class="text-2xl font-black text-gray-900 font-sans tracking-tight">ብር 12,450.00</div>
                                </div>

                                <!-- Recent Activity List -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-bold text-gray-900">የቅርብ ጊዜ እንቅስቃሴዎች</div>
                                        <div class="text-xs text-blue-600 font-bold">ሁሉንም እይ</div>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <!-- Item 1 -->
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-transparent hover:border-gray-200 transition">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-white shadow-sm flex items-center justify-center text-blue-700">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900">ወርሃዊ መዋጮ</div>
                                                    <div class="text-xs text-gray-500">ከ 2 ቀናት በፊት</div>
                                                </div>
                                            </div>
                                            <div class="text-sm font-bold text-gray-900">+ ብር 500</div>
                                        </div>

                                        <!-- Item 2 -->
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-transparent hover:border-gray-200 transition">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-white shadow-sm flex items-center justify-center text-gray-700">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900">የካሳ ጥያቄ</div>
                                                    <div class="text-xs text-gray-500">በኮሚቴ እየታየ ነው</div>
                                                </div>
                                            </div>
                                            <div class="text-sm font-bold text-gray-900">ብር 10,000</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Bento Grid Features (Fixed contrast/visibility) -->
        <section id="features" class="py-24 bg-white border-y border-gray-200/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 sm:px-8">
                
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-3xl font-bold text-gray-900 tracking-tight mb-4">
                        የእድር አስተዳደር በአንድ ቦታ
                    </h2>
                    <p class="text-lg text-gray-600 leading-loose">
                        ከአባላት ምዝገባ እስከ ካሳ ክፍያ፣ ሁሉንም መረጃዎች በዘመናዊ እና ደህንነቱ በተጠበቀ መንገድ ያስተዳድሩ።
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    
                    <!-- Feature 1 (Large) -->
                    <div class="bg-gray-50 border border-gray-200 rounded-3xl p-4 sm:p-8 md:col-span-2 lg:col-span-2 lg:row-span-2 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between group">
                        <div class="space-y-6">
                            <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">ዲጂታል ክፍያና ፈንድ</h3>
                            <p class="text-gray-600 leading-loose text-lg">
                                አባላት መዋጮቸውን በቴሌብር፣ በቻፓ ወይም በባንክ ዝውውር ሲፈጽሙ ሲስተሙ በቀጥታ መዝግቦ ኦፊሴላዊ ዲጂታል ደረሰኝ ያመነጫል። የጥሬ ገንዘብ ክፍያዎችም በተመሳሳይ በሲስተሙ ይመዘገባሉ።
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white border border-gray-200 rounded-3xl p-4 sm:p-8 md:col-span-1 lg:col-span-2 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 text-black flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">ግልጽ የካሳ ማጽደቅ ሂደት</h3>
                            <p class="text-gray-600 leading-loose">
                                የቀብር ወይም የሰርግ ጥያቄዎች ሲቀርቡ የኮሚቴ አባላት በጋራ አይተው ያጸድቃሉ። ውሳኔው ለአባላቱ ግልጽ ሆኖ ይቀመጣል።
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white border border-gray-200 rounded-3xl p-4 sm:p-8 shadow-sm hover:shadow-md transition duration-300">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">ትክክለኛ ሪፖርት</h3>
                            <p class="text-gray-600 leading-loose text-sm">
                                የገቢና ወጪ ሚዛን በማንኛውም ጊዜ በትክክል ይሰላል።
                            </p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-white border border-gray-200 rounded-3xl p-4 sm:p-8 shadow-sm hover:shadow-md transition duration-300">
                        <div class="space-y-4">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">ኤስ ኤም ኤስ</h3>
                            <p class="text-gray-600 leading-loose text-sm">
                                አስፈላጊ መረጃዎች በአጭር የጽሁፍ መልዕክት በፍጥነት ይደርሳሉ።
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        
        <!-- Call to Action -->
        <section class="py-24 relative overflow-hidden">
            <div class="absolute inset-0 bg-blue-900"></div>
            <div class="absolute inset-0 hero-pattern opacity-10"></div>
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-4 sm:px-8 text-center relative z-10">
                <h2 class="text-3xl sm:text-3xl font-bold text-white tracking-tight mb-6">
                    የእድርዎን አስተዳደር ዛሬውኑ ያሳድጉ
                </h2>
                <p class="text-lg text-blue-200 leading-loose mb-10 max-w-2xl mx-auto">
                    አካላዊ መዝገቦችን ወደ ዲጂታል ይቀይሩ። በነፃ በመመዝገብ መድረኩን መጠቀም ይጀምሩ።
                </p>
                <div class="flex justify-center">
                    <a href="{{ route('register') }}" class="px-4 sm:px-8 py-4 rounded-full bg-white text-blue-900 font-bold hover:bg-gray-100 transition shadow-lg flex items-center gap-2">
                        እድርዎን ይመዝገቡ <span class="text-blue-400">&rarr;</span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 sm:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <x-logo class="h-6" />
                </div>
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} እድር ፕላትፎርም። መብቱ በሕግ የተጠበቀ ነው።</p>
        </div>
    </footer>

</body>
</html>