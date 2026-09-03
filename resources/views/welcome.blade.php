<!DOCTYPE html>
<html lang="am" class="h-full bg-[#090D16] text-slate-100 antialiased selection:bg-emerald-500 selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>እድር (Idir) — የኢትዮጵያ ማህበራዊ ዋስትናና የእድር አስተዳደር ዲጂታል ፕላትፎርም</title>
    
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
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        },
                        gold: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                        surface: {
                            900: '#0c1322',
                            800: '#111b30',
                            700: '#1a2744',
                        }
                    },
                    fontFamily: {
                        sans: ['"Noto Sans Ethiopic"', '"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts: Noto Sans Ethiopic & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Ethiopic', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .ethiopian-pattern-top {
            background: linear-gradient(90deg, #10b981 0%, #f59e0b 50%, #059669 100%);
            height: 3px;
        }
        .hero-glow {
            background: radial-gradient(circle at 50% 10%, rgba(16, 185, 129, 0.15) 0%, rgba(6, 78, 59, 0.05) 45%, transparent 70%);
        }
        .glass-card {
            background: rgba(17, 27, 48, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }
        .glass-card:hover {
            border-color: rgba(16, 185, 129, 0.35);
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-[#090D16] text-slate-100 antialiased overflow-x-hidden">

    <!-- Top Traditional Geometric Accent Bar -->
    <div class="ethiopian-pattern-top w-full sticky top-0 z-50"></div>

    <!-- Navigation Header -->
    <header class="sticky top-[3px] z-40 bg-[#090D16]/85 backdrop-blur-xl border-b border-slate-800/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-20 flex items-center justify-between gap-4">
                
                <!-- Brand Identity -->
                <a href="/" class="flex items-center gap-3.5 group shrink-0">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-700 via-brand-600 to-teal-400 p-0.5 shadow-lg shadow-brand-900/40 group-hover:scale-105 transition-transform">
                        <div class="w-full h-full bg-slate-950/70 backdrop-blur rounded-[14px] flex items-center justify-center text-white font-black text-xl">
                            እ
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-extrabold tracking-tight text-white">እድር</span>
                            <span class="text-[10px] bg-brand-950 text-brand-300 border border-brand-500/30 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                ዲጂታል SaaS
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium hidden sm:block">
                            የኢትዮጵያ ማህበራዊ ዋስትና መድረክ
                        </p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center gap-1 xl:gap-2">
                    <a href="#features" class="text-xs sm:text-sm font-semibold text-slate-300 hover:text-white px-3.5 py-2 rounded-xl hover:bg-slate-800/60 transition">
                        አገልግሎቶች (Features)
                    </a>
                    <a href="#how-it-works" class="text-xs sm:text-sm font-semibold text-slate-300 hover:text-white px-3.5 py-2 rounded-xl hover:bg-slate-800/60 transition">
                        አሰራር (Workflow)
                    </a>
                    <a href="{{ route('member.lookup') }}" class="text-xs sm:text-sm font-semibold text-emerald-400 hover:text-emerald-300 px-3.5 py-2 rounded-xl hover:bg-emerald-950/40 border border-emerald-500/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>ሁኔታ ማረጋገጫ (Lookup)</span>
                    </a>
                </nav>

                <!-- Action Controls -->
                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ route('member.login') }}" class="text-xs sm:text-sm font-bold text-slate-300 hover:text-white px-3.5 py-2 rounded-xl hover:bg-slate-800/60 transition">
                        የአባላት መግቢያ
                    </a>
                    <a href="/committee/login" class="text-xs sm:text-sm font-bold text-slate-300 hover:text-white px-3.5 py-2 rounded-xl hover:bg-slate-800/60 transition flex items-center gap-1">
                        <span>የኮሚቴ መግቢያ</span>
                    </a>
                    <a href="{{ route('register') }}" class="text-xs sm:text-sm font-black bg-gradient-to-r from-brand-600 to-teal-600 hover:from-brand-500 hover:to-teal-500 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-brand-950/60 transition transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5">
                        <span>አዲስ እድር ይጀምሩ</span>
                        <span class="font-sans">&rarr;</span>
                    </a>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <div class="flex items-center gap-2 lg:hidden">
                    <a href="{{ route('member.lookup') }}" class="text-xs font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 px-2.5 py-1.5 rounded-lg">
                        ፍለጋ
                    </a>
                    <button type="button" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="p-2 text-slate-400 hover:text-white rounded-xl bg-slate-800/80 border border-slate-700 focus:outline-none" aria-label="Toggle navigation">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Dropdown -->
            <div id="mobile-nav" class="hidden lg:hidden pb-5 pt-3 border-t border-slate-800/80 space-y-2">
                <a href="#features" class="block text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 rounded-lg hover:bg-slate-800 transition">
                    ✨ አገልግሎቶች (Features)
                </a>
                <a href="#how-it-works" class="block text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 rounded-lg hover:bg-slate-800 transition">
                    🔄 አሰራር (Workflow)
                </a>
                <a href="{{ route('member.lookup') }}" class="block text-sm font-semibold text-emerald-400 hover:text-white px-3 py-2 rounded-lg hover:bg-emerald-950/60 transition">
                    🔍 ፈጣን የአባልነትና ክፍያ ፍለጋ (Lookup)
                </a>
                <a href="{{ route('member.login') }}" class="block text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 rounded-lg hover:bg-slate-800 transition">
                    👤 የአባላት ፖርታል መግቢያ (Member Login)
                </a>
                <a href="/committee/login" class="block text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 rounded-lg hover:bg-slate-800 transition">
                    ⚙️ የኮሚቴ አስተዳደር ዳሽቦርድ (Committee Dashboard)
                </a>
                <div class="pt-2">
                    <a href="{{ route('register') }}" class="block text-center text-sm font-black bg-gradient-to-r from-brand-600 to-teal-600 text-white px-4 py-3 rounded-xl shadow-lg transition">
                        አዲስ እድር በነፃ ይመዝግቡ (Sign Up) &rarr;
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container with Hero Background Glow -->
    <main class="hero-glow relative flex-1">
        
        <!-- Ambient Decorative Lighting Elements -->
        <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-brand-600/10 blur-[130px] pointer-events-none rounded-full"></div>
        <div class="absolute top-40 right-10 w-[300px] h-[300px] bg-amber-500/5 blur-[120px] pointer-events-none rounded-full"></div>

        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-16 lg:pt-20 lg:pb-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Column: Value Proposition & CTAs -->
                <div class="lg:col-span-7 space-y-7 text-center lg:text-left">
                    
                    <!-- Trust Badge -->
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-emerald-950/80 border border-emerald-500/30 text-emerald-300 text-xs font-bold uppercase tracking-wider shadow-inner">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>የኢትዮጵያ እድር ማህበራት ዲጂታል አስተዳደር ሥርዓት</span>
                    </div>

                    <!-- Main Focal Headline -->
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.18]">
                        የእድርዎን አስተዳደር <br>
                        <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-amber-300 bg-clip-text text-transparent">
                            በዘመናዊ ዲጂታል ቴክኖሎጂ
                        </span>
                        ያቀላጥፉ
                    </h1>

                    <!-- Refined Subhead -->
                    <p class="text-base sm:text-lg text-slate-300 font-normal leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        የወር መዋጮ በቴሌብር እና በካርድ (Chapa)፣ ባለብዙ-ደረጃ የካሳ ማጽደቅ፣ አውቶሜትድ የኤስኤምኤስ (AfroMessage) ማሳወቂያዎች እና የማይበረዝ የሒሳብ መዝገብ — ሁሉንም በአንድ ታማኝ መድረክ።
                    </p>

                    <!-- CTAs with Distinct Visual Priority -->
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <!-- Primary CTA -->
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-brand-600 via-brand-500 to-teal-600 hover:from-brand-500 hover:to-teal-500 text-white font-black text-base rounded-2xl shadow-xl shadow-brand-950/70 transition transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2">
                            <span>እድርዎን በነፃ ይመዝግቡ</span>
                            <span class="font-sans">&rarr;</span>
                        </a>

                        <!-- Secondary CTA -->
                        <a href="{{ route('member.lookup') }}" class="w-full sm:w-auto px-7 py-4 bg-slate-900/80 hover:bg-slate-800 text-slate-200 hover:text-white font-bold text-base rounded-2xl border border-slate-700/80 hover:border-slate-600 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>በስልክ ቁጥር ክፍያ ይፈልጉ</span>
                        </a>
                    </div>

                    <!-- Key Security & Operational Guarantees -->
                    <div class="pt-6 border-t border-slate-800/80 grid grid-cols-3 gap-4 text-center lg:text-left">
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-white block">100%</span>
                            <span class="text-xs text-slate-400 font-medium">የማይበረዝ የሒሳብ መዝገብ</span>
                        </div>
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-white block">24/7</span>
                            <span class="text-xs text-slate-400 font-medium">የቴሌብርና ካርድ ክፍያ</span>
                        </div>
                        <div>
                            <span class="text-xl sm:text-2xl font-black text-white block">0 ብር</span>
                            <span class="text-xs text-slate-400 font-medium">የመጀመሪያ ምዝገባ ክፍያ</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Live Product Mockup -->
                <div class="lg:col-span-5 relative">
                    <div class="glass-card rounded-3xl p-6 sm:p-7 shadow-2xl relative overflow-hidden border border-slate-700/60">
                        
                        <!-- Ambient header glow inside card -->
                        <div class="absolute top-0 right-0 w-48 h-48 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

                        <!-- Mockup Top Bar -->
                        <div class="flex items-center justify-between pb-5 border-b border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white flex items-center justify-center font-black text-lg shadow">
                                    ሰ
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-white">ሰላም የሰፈር እድር</h3>
                                    <p class="text-[11px] text-slate-400">የስራ አስፈጻሚ ኮሚቴ ዳሽቦርድ</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 bg-emerald-950 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                ንቁ (Active)
                            </span>
                        </div>

                        <!-- Mockup Fund Balance Strip -->
                        <div class="my-5 p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">የእድሩ አጠቃላይ ፈንድ ሚዛን</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-2xl sm:text-3xl font-black text-white tracking-tight">124,500.00 <span class="text-sm text-emerald-400 font-bold">ብር</span></span>
                                <span class="text-xs font-semibold text-emerald-400 bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-500/20">
                                    +12 አባላት በዚህ ወር
                                </span>
                            </div>
                        </div>

                        <!-- Mockup Multi-Approver Pipeline Preview -->
                        <div class="space-y-3">
                            <span class="text-xs font-bold text-slate-300 block">የካሳ ጥያቄ ማጽደቅ ሂደት (Claims Pipeline)</span>
                            <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-white block">የቀብር ድጋፍ ጥያቄ (#CLM-084)</span>
                                    <span class="text-slate-400 text-[11px]">አባል፦ ታደለ ወርቁ &bull; 10,000.00 ብር</span>
                                </div>
                                <span class="px-2 py-1 bg-amber-950/80 text-amber-300 border border-amber-500/30 font-bold text-[10px] rounded-lg">
                                    2 ፈቃዶች ተሟልተዋል ✓
                                </span>
                            </div>
                        </div>

                        <!-- Mockup Instant Notification Toast -->
                        <div class="mt-4 p-3 rounded-xl bg-brand-950/40 border border-brand-500/30 flex items-center gap-3 text-xs text-brand-200">
                            <span class="text-base">📲</span>
                            <div class="truncate">
                                <span class="font-bold block text-white text-[11px]">የኤስኤምኤስ ማሳወቂያ ተልኳል</span>
                                <span class="text-[10px] text-slate-400 truncate block">"የወር መዋጮ 200.00 ብር በቴሌብር ገቢ ሆኗል..."</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Ecosystem & Trust Partners Strip -->
        <section class="border-y border-slate-800/80 bg-slate-950/40 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block">
                    የተቀናጁ የክፍያና ማረጋገጫ ቴክኖሎጂዎች (Integrated Ecosystem)
                </span>
                <div class="flex flex-wrap items-center justify-center gap-8 sm:gap-14 text-slate-400 font-bold text-sm">
                    <div class="flex items-center gap-2 hover:text-white transition">
                        <span class="text-emerald-400 font-black text-base">●</span>
                        <span>ቴሌብር (Telebirr)</span>
                    </div>
                    <div class="flex items-center gap-2 hover:text-white transition">
                        <span class="text-amber-400 font-black text-base">●</span>
                        <span>ሲቢኢ ብር (CBE Birr)</span>
                    </div>
                    <div class="flex items-center gap-2 hover:text-white transition">
                        <span class="text-blue-400 font-black text-base">●</span>
                        <span>ቻፓ ክፍያ (Chapa API)</span>
                    </div>
                    <div class="flex items-center gap-2 hover:text-white transition">
                        <span class="text-teal-400 font-black text-base">●</span>
                        <span>አፍሮሜሴጅ (AfroMessage SMS)</span>
                    </div>
                    <div class="flex items-center gap-2 hover:text-white transition">
                        <span class="text-purple-400 font-black text-base">●</span>
                        <span>ፋይዳ መታወቂያ (Fayda Digital ID)</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4 Distinct Feature Pillars Section -->
        <section id="features" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-2xl mx-auto space-y-3 mb-14">
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider bg-emerald-950/80 px-3 py-1 rounded-full border border-emerald-500/20">
                    ዋና ዋና አገልግሎቶች
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    ለእድር አስተዳዳሪዎችና አባላት የተሟላ መፍትሔ
                </h2>
                <p class="text-sm text-slate-400 leading-relaxed">
                    ባህላዊውን የእድር እሴት ሳያዛንፉ በዲጂታል ግልጽነት እና ፈጣን አገልግሎት የታገዘ ዘመናዊ መድረክ።
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Pillar 1: Digital Dues & Payments -->
                <div class="glass-card rounded-3xl p-6 space-y-4 hover:-translate-y-1 transition duration-300 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-950 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xl font-bold">
                            💳
                        </div>
                        <h3 class="text-lg font-black text-white">ዲጂታል ክፍያና ፈንድ</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            አባላት በቴሌብር እና በባንክ ካርድ በቻፓ ክፍያ ይፈጽማሉ። በጥሬ ገንዘብ የተከፈለም ወዲያውኑ በደረሰኝ ይስተናገዳል።
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-800 text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                        <span>ኦፊሴላዊ ደረሰኝ ማመንጫ</span> &rarr;
                    </div>
                </div>

                <!-- Pillar 2: Multi-Approver Claims Workflow -->
                <div class="glass-card rounded-3xl p-6 space-y-4 hover:-translate-y-1 transition duration-300 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-teal-950 text-teal-300 border border-teal-500/30 flex items-center justify-center text-xl font-bold">
                            ⚖️
                        </div>
                        <h3 class="text-lg font-black text-white">የካሳ ማጽደቅ ሂደት</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            የቀብር፣ የሰርግ ወይም የድንገተኛ አደጋ ድጋፎች በኮሚቴው (ሰብሳቢ፣ ጸሐፊ፣ ገንዘብ ያዥ) በጋራ ታይተው ይጸድቃሉ።
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-800 text-[11px] text-teal-300 font-semibold flex items-center gap-1">
                        <span>4-ደረጃ የክትትል መስኮት</span> &rarr;
                    </div>
                </div>

                <!-- Pillar 3: Immutable Ledger Accounting -->
                <div class="glass-card rounded-3xl p-6 space-y-4 hover:-translate-y-1 transition duration-300 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-950 text-amber-300 border border-amber-500/30 flex items-center justify-center text-xl font-bold">
                            📊
                        </div>
                        <h3 class="text-lg font-black text-white">የማይበረዝ የሒሳብ መዝገብ</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            የእድሩ የፋይናንስ ሚዛን የሚሰላው ከገቢና ወጪ ቀጥተኛ ስሌት ብቻ ነው። የትኛውም የሒሳብ ታሪክ አይደለዝም።
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-800 text-[11px] text-amber-300 font-semibold flex items-center gap-1">
                        <span>100% ግልጽ የሒሳብ ሰነድ</span> &rarr;
                    </div>
                </div>

                <!-- Pillar 4: Instant Automated SMS Notifications -->
                <div class="glass-card rounded-3xl p-6 space-y-4 hover:-translate-y-1 transition duration-300 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-950 text-indigo-300 border border-indigo-500/30 flex items-center justify-center text-xl font-bold">
                            📲
                        </div>
                        <h3 class="text-lg font-black text-white">ፈጣን የኤስኤምኤስ ማሳወቂያ</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            ክፍያ ሲፈጸም፣ የካሳ ውሳኔ ሲሰጥ ወይም አስቸኳይ ስብሰባ ሲጠራ ለእያንዳንዱ አባል ፈጣን የጽሑፍ መልዕክት ይደርሳል።
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-800 text-[11px] text-indigo-300 font-semibold flex items-center gap-1">
                        <span>አውቶሜትድ ማሳወቂያ</span> &rarr;
                    </div>
                </div>

            </div>
        </section>

        <!-- 4-Step How It Works Section -->
        <section id="how-it-works" class="border-t border-slate-800/80 bg-slate-950/50 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto space-y-3 mb-16">
                    <span class="text-xs font-bold text-teal-400 uppercase tracking-wider bg-teal-950/80 px-3 py-1 rounded-full border border-teal-500/20">
                        ቀላልና ፈጣን አሰራር
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                        በ4 ቀላል ደረጃዎች እድርዎን ዲጂታል ያድርጉ
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                    
                    <div class="space-y-3 relative">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-black text-sm flex items-center justify-center shadow-md">
                            1
                        </div>
                        <h4 class="text-base font-bold text-white">እድርዎን ይመዝግቡ</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            የእድሩን ስም፣ የወር መዋጮ መጠን፣ የብቃት ጊዜ እና የካሳ ዓይነቶችን በደቂቃዎች ውስጥ ያዋቅሩ።
                        </p>
                    </div>

                    <div class="space-y-3 relative">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-black text-sm flex items-center justify-center shadow-md">
                            2
                        </div>
                        <h4 class="text-base font-bold text-white">አባላትን ያካትቱ</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            አባላትን በስልክ ቁጥራቸው ያስገቡ፤ የስራ ድርሻዎችን (ጸሐፊ፣ ገንዘብ ያዥ) ለኮሚቴ አባላት ይመድቡ።
                        </p>
                    </div>

                    <div class="space-y-3 relative">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-black text-sm flex items-center justify-center shadow-md">
                            3
                        </div>
                        <h4 class="text-base font-bold text-white">ክፍያና ካሳ ያስተዳድሩ</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            አባላት በቴሌብር ይከፍላሉ፤ ኮሚቴው የቀረቡ የካሳ ጥያቄዎችን በሕጉ መሰረት በጋራ ያጸድቃል።
                        </p>
                    </div>

                    <div class="space-y-3 relative">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-black text-sm flex items-center justify-center shadow-md">
                            4
                        </div>
                        <h4 class="text-base font-bold text-white">ግልጽ ሪፖርት ያግኙ</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            የፈንድ ሚዛኑ በቅጽበት ይስተካከላል፤ ኦፊሴላዊ ደረሰኞችና የሒሳብ መዝገቦች በማንኛውም ሰዓት ይገኛሉ።
                        </p>
                    </div>

                </div>
            </div>
        </section>

        <!-- Final Call to Action Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
            <div class="glass-card rounded-3xl p-8 sm:p-12 text-center relative overflow-hidden border border-brand-500/30">
                <div class="max-w-2xl mx-auto space-y-6">
                    <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                        የእድርዎን አስተዳደር ዛሬውኑ ወደ ዘመናዊ ደረጃ ያሳድጉ
                    </h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        የእድር ምዝገባ ምንም ዓይነት ክፍያ አያስፈልገውም። ፕላትፎርሙን በነፃ መጠቀም ይጀምሩ።
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-brand-600 to-teal-600 hover:from-brand-500 hover:to-teal-500 text-white font-black text-base rounded-2xl shadow-xl shadow-brand-950/80 transition transform hover:-translate-y-0.5 active:scale-95">
                            እድርዎን በነፃ ይመዝግቡ (Sign Up) &rarr;
                        </a>
                        <a href="{{ route('member.lookup') }}" class="w-full sm:w-auto px-8 py-4 bg-slate-900 hover:bg-slate-800 text-slate-200 font-bold text-base rounded-2xl border border-slate-700 transition">
                            የአባልነት ሁኔታ ይፈልጉ
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 bg-[#070A11] py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 text-xs text-slate-400">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-brand-700 text-white flex items-center justify-center font-black text-sm">
                        እ
                    </div>
                    <div>
                        <span class="font-bold text-white text-sm block">እድር (Idir Management Platform)</span>
                        <span>የኢትዮጵያ ማህበራዊ ዋስትና ዲጂታል መድረክ</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6 font-semibold">
                    <a href="{{ route('member.lookup') }}" class="hover:text-white transition">ፈጣን ፍለጋ</a>
                    <a href="{{ route('member.login') }}" class="hover:text-white transition">የአባላት ፖርታል</a>
                    <a href="/committee/login" class="hover:text-white transition">የኮሚቴ ዳሽቦርድ</a>
                    <a href="/admin/login" class="hover:text-white transition">አስተዳዳሪ</a>
                </div>

                <p class="text-center md:text-right text-slate-500">
                    &copy; {{ date('Y') }} እድር ፕላትፎርም። መብቱ በሕግ የተጠበቀ ነው።
                </p>
            </div>
        </div>
    </footer>

</body>
</html>

