@php
    $tenant = $this->getTenant();
@endphp

@if ($tenant && !$tenant->isActive())
    <div class="mb-6">
        @if ($tenant->isPendingApproval())
            <div class="p-6 rounded-3xl bg-gray-500/10 border border-gray-500/30 text-gray-950 dark:text-gray-100 flex flex-col md:flex-row items-start md:items-center gap-5 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                <div class="w-14 h-14 bg-gray-500 text-white rounded-2xl flex items-center justify-center shrink-0 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-500/20 text-gray-800 dark:text-gray-300 text-xs font-bold uppercase tracking-wider">
                        በግምገማ ላይ
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        የእድርዎ ምዝገባ በፕላትፎርም አስተዳዳሪው በመገምገም ላይ ነው
                    </h2>
                    <p class="text-sm text-gray-800/90 dark:text-gray-200/80 leading-relaxed max-w-3xl font-medium">
                        የ <strong>{{ $tenant->name }}</strong> መረጃ፣ የመዋጮና የካሳ ደንቦች ለፕላትፎርም ባለቤቱ ለግምገማ ቀርቧል። አንዴ እንደተረጋገጠና እንደጸደቀ አባላትን መመዝገብ፣ መዋጮ መሰብሰብና አገልግሎቱን መጠቀም ይችላሉ። ውሳኔው እንደተሰጠ በስልክዎ (SMS) ማሳወቂያ ይደርስዎታል።
                    </p>
                </div>
            </div>
        @elseif ($tenant->isRejected())
            <div class="p-6 rounded-3xl bg-gray-950/30 border border-gray-900/500/10 border border-gray-800 text-gray-500 dark:text-gray-500 flex flex-col md:flex-row items-start md:items-center gap-5 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                <div class="w-14 h-14 bg-gray-600 shadow-none text-white rounded-2xl flex items-center justify-center shrink-0 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-950/30 border border-gray-900/500/20 text-black dark:text-black text-xs font-bold uppercase tracking-wider">
                        ውድቅ ተደርጓል
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-500 dark:text-gray-500">
                        የእድር ምዝገባው በፕላትፎርም አስተዳዳሪው ውድቅ ተደርጓል
                    </h2>
                    <div class="p-3 bg-gray-900/50/60 dark:bg-gray-950/40 rounded-xl border border-gray-800 dark:border-gray-800 text-sm font-medium text-gray-500 dark:text-gray-500">
                        <strong>የተሰጠ ምክንያት፡</strong> {{ $tenant->rejection_reason ?? 'ምክንያት አልተገለጸም' }}
                    </div>
                    <p class="text-xs text-black dark:text-black font-medium">
                        እባክዎ ተጨማሪ መረጃ ለማግኘት ወይም ደንብዎን አስተካክለው እንደገና ለማቅረብ የፕላትፎርም አስተዳዳሪውን ያነጋግሩ።
                    </p>
                </div>
            </div>
        @elseif ($tenant->isSuspended())
            <div class="p-6 rounded-3xl bg-gray-950/30 border border-gray-900/500/10 border border-gray-800 text-gray-500 dark:text-gray-500 flex flex-col md:flex-row items-start md:items-center gap-5 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                <div class="w-14 h-14 bg-gray-600 shadow-none text-white rounded-2xl flex items-center justify-center shrink-0 shadow-[0_0_10px_rgba(0,0,0,0.5)]">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-950/30 border border-gray-900/500/20 text-black dark:text-black text-xs font-bold uppercase tracking-wider">
                        የታገደ
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-500 dark:text-gray-500">
                        ይህ እድር በፕላትፎርም አስተዳዳሪው በጊዜያዊነት ታግዷል
                    </h2>
                    <p class="text-sm text-black/90 dark:text-gray-500/80 leading-relaxed font-medium">
                        የዚህ እድር አገልግሎት በጊዜያዊነት ታግዷል። እባክዎ ለዝርዝር መረጃ የፕላትፎርም አስተዳዳሪውን ያነጋግሩ።
                    </p>
                </div>
            </div>
        @endif
    </div>
@endif

