<!DOCTYPE html>
<html lang="am" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $idir->name ?? 'እድር' }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="overflow-x-hidden bg-gray-50 text-gray-900 antialiased font-sans h-full flex flex-col items-center justify-center p-4">
    <div class="mb-8">
        <a href="/"><x-logo class="h-10" /></a>
    </div>
    
    <div class="max-w-md w-full bg-white rounded-3xl border border-gray-200 shadow-sm p-4 sm:p-8 text-center space-y-4">
        @if (isset($idir) && $idir->isPendingApproval())
            <div class="w-16 h-16 bg-gray-100 text-black rounded-2xl flex items-center justify-center mx-auto text-2xl font-black shadow-inner">
                ⏳
            </div>
            <h1 class="text-xl font-black text-gray-900">ይህ እድር በግምገማ ላይ ነው</h1>
            <p class="text-xs text-gray-500 leading-loose">
                የ <strong>{{ $idir->name ?? 'እድሩ' }}</strong> ምዝገባ በፕላትፎርም አስተዳዳሪው በመገምገም ላይ ነው። አንዴ እንደጸደቀ አገልግሎቱ ይጀምራል።
            </p>
        @elseif (isset($idir) && $idir->isRejected())
            <div class="w-16 h-16 bg-gray-50 border border-gray-300 text-black rounded-2xl flex items-center justify-center mx-auto text-2xl font-black shadow-inner">
                ✕
            </div>
            <h1 class="text-xl font-black text-gray-900">የእድር ምዝገባው ውድቅ ተደርጓል</h1>
            <p class="text-xs text-gray-500 leading-loose">
                የ <strong>{{ $idir->name ?? 'እድሩ' }}</strong> ምዝገባ ጥያቄ ውድቅ ተደርጓል።
            </p>
            @if ($idir->rejection_reason)
                <div class="p-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-semibold text-black">
                    ምክንያት፡ {{ $idir->rejection_reason }}
                </div>
            @endif
        @else
            <div class="w-16 h-16 bg-gray-50 border border-gray-300 text-black rounded-2xl flex items-center justify-center mx-auto text-2xl font-black shadow-inner">
                ⚠️
            </div>
            <h1 class="text-xl font-black text-gray-900">ይህ እድር በጊዜያዊነት ታግዷል</h1>
            <p class="text-xs text-gray-500 leading-loose">
                የ <strong>{{ $idir->name ?? 'እድሩ' }}</strong> አገልግሎት በፕላትፎርም አስተዳዳሪው በጊዜያዊነት ታግዷል። እባክዎ የፕላትፎርም አስተዳዳሪውን ወይም የእድሩን ሰብሳቢ ያነጋግሩ።
            </p>
        @endif

        <div class="border-t border-gray-200 pt-4">
            <a href="{{ route('member.login') }}" class="text-xs font-bold text-blue-600 hover:underline">
                &larr; ወደ መግቢያ ገጽ ተመለስ
            </a>
        </div>
    </div>
</body>
</html>
