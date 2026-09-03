@php
    $tenant = $this->getTenant();
@endphp

@if ($tenant && !$tenant->isActive())
    <div class="mb-6">
        @if ($tenant->isPendingApproval())
            <div class="p-6 rounded-3xl bg-amber-500/10 border-2 border-amber-500/30 text-amber-950 dark:text-amber-100 flex flex-col md:flex-row items-start md:items-center gap-5 shadow-lg shadow-amber-500/5">
                <div class="w-14 h-14 bg-amber-500 text-white rounded-2xl flex items-center justify-center text-3xl font-black shrink-0 shadow-md">
                    ⏳
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider">
                        በግምገማ ላይ (Pending Approval)
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-amber-900 dark:text-amber-100">
                        የእድርዎ ምዝገባ በፕላትፎርም አስተዳዳሪው በመገምገም ላይ ነው
                    </h2>
                    <p class="text-sm text-amber-800/90 dark:text-amber-200/80 leading-relaxed max-w-3xl">
                        የ <strong>{{ $tenant->name }}</strong> መረጃ፣ የመዋጮና የካሳ ደንቦች ለፕላትፎርም ባለቤቱ ለግምገማ ቀርቧል። አንዴ እንደተረጋገጠና እንደጸደቀ አባላትን መመዝገብ፣ መዋጮ መሰብሰብና አገልግሎቱን መጠቀም ይችላሉ። ውሳኔው እንደተሰጠ በስልክዎ (SMS) ማሳወቂያ ይደርስዎታል።
                    </p>
                </div>
            </div>
        @elseif ($tenant->isRejected())
            <div class="p-6 rounded-3xl bg-red-500/10 border-2 border-red-500/30 text-red-950 dark:text-red-100 flex flex-col md:flex-row items-start md:items-center gap-5 shadow-lg shadow-red-500/5">
                <div class="w-14 h-14 bg-red-600 text-white rounded-2xl flex items-center justify-center text-3xl font-black shrink-0 shadow-md">
                    ✕
                </div>
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 text-red-800 dark:text-red-300 text-xs font-black uppercase tracking-wider">
                        ውድቅ ተደርጓል (Registration Rejected)
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-red-900 dark:text-red-100">
                        የእድር ምዝገባው በፕላትፎርም አስተዳዳሪው ውድቅ ተደርጓል
                    </h2>
                    <div class="p-3 bg-red-100/60 dark:bg-red-950/40 rounded-xl border border-red-200 dark:border-red-800 text-sm font-semibold text-red-900 dark:text-red-200">
                        <strong>የተሰጠ ምክንያት (Rejection Reason)፡</strong> {{ $tenant->rejection_reason ?? 'ምክንያት አልተገለጸም' }}
                    </div>
                    <p class="text-xs text-red-700 dark:text-red-300">
                        እባክዎ ተጨማሪ መረጃ ለማግኘት ወይም ደንብዎን አስተካክለው እንደገና ለማቅረብ የፕላትፎርም አስተዳዳሪውን ያነጋግሩ።
                    </p>
                </div>
            </div>
        @elseif ($tenant->isSuspended())
            <div class="p-6 rounded-3xl bg-red-500/10 border-2 border-red-500/30 text-red-950 dark:text-red-100 flex flex-col md:flex-row items-start md:items-center gap-5 shadow-lg shadow-red-500/5">
                <div class="w-14 h-14 bg-red-600 text-white rounded-2xl flex items-center justify-center text-3xl font-black shrink-0 shadow-md">
                    ⚠️
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 text-red-800 dark:text-red-300 text-xs font-black uppercase tracking-wider">
                        የታገደ (Suspended)
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-red-900 dark:text-red-100">
                        ይህ እድር በፕላትፎርም አስተዳዳሪው በጊዜያዊነት ታግዷል
                    </h2>
                    <p class="text-sm text-red-800/90 dark:text-red-200/80 leading-relaxed">
                        የዚህ እድር አገልግሎት በጊዜያዊነት ታግዷል። እባክዎ ለዝርዝር መረጃ የፕላትፎርም አስተዳዳሪውን ያነጋግሩ።
                    </p>
                </div>
            </div>
        @endif
    </div>
@endif
