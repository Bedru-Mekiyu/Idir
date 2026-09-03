<!DOCTYPE html>
<html lang="am" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $idir->name ?? 'እድር' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 text-center space-y-4">
        @if (isset($idir) && $idir->isPendingApproval())
            <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto text-2xl font-black shadow-inner">
                ⏳
            </div>
            <h1 class="text-xl font-black text-slate-900">ይህ እድር በግምገማ ላይ ነው</h1>
            <p class="text-xs text-slate-600 leading-relaxed">
                የ <strong>{{ $idir->name ?? 'እድሩ' }}</strong> ምዝገባ በፕላትፎርም አስተዳዳሪው በመገምገም ላይ ነው። አንዴ እንደጸደቀ አገልግሎቱ ይጀምራል።
            </p>
        @elseif (isset($idir) && $idir->isRejected())
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto text-2xl font-black shadow-inner">
                ✕
            </div>
            <h1 class="text-xl font-black text-slate-900">የእድር ምዝገባው ውድቅ ተደርጓል</h1>
            <p class="text-xs text-slate-600 leading-relaxed">
                የ <strong>{{ $idir->name ?? 'እድሩ' }}</strong> ምዝገባ ጥያቄ ውድቅ ተደርጓል።
            </p>
            @if ($idir->rejection_reason)
                <div class="p-3 bg-red-50 rounded-xl border border-red-100 text-xs font-semibold text-red-800">
                    ምክንያት፡ {{ $idir->rejection_reason }}
                </div>
            @endif
        @else
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto text-2xl font-black shadow-inner">
                ⚠️
            </div>
            <h1 class="text-xl font-black text-slate-900">ይህ እድር በጊዜያዊነት ታግዷል</h1>
            <p class="text-xs text-slate-600 leading-relaxed">
                የ <strong>{{ $idir->name ?? 'እድሩ' }}</strong> አገልግሎት በፕላትፎርም አስተዳዳሪው በጊዜያዊነት ታግዷል። እባክዎ የፕላትፎርም አስተዳዳሪውን ወይም የእድሩን ሰብሳቢ ያነጋግሩ።
            </p>
        @endif

        <div class="border-t border-slate-100 pt-4">
            <a href="{{ route('member.login') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                &larr; ወደ መግቢያ ገጽ ተመለስ
            </a>
        </div>
    </div>
</body>
</html>
