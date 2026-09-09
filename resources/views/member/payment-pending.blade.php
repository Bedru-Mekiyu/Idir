@extends('layouts.member')
@section('content')

    <div class="max-w-2xl mx-auto">
        <!-- Pending notice -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-10 text-center mb-8">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center mb-5">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-2">የክፍያ ጥያቄ ተልኳል</h1>
            <p class="text-gray-600 leading-relaxed">
                የክፍያ ማረጋገጫ ጥያቄ ወደ ስልክዎ ተልኳል። እባክዎ በስልክዎ ላይ ያረጋግጡ።
            </p>
        </div>

        <!-- Transaction details -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">የክፍያ ዝርዝር</h2>
            <dl class="divide-y divide-gray-100">
                <div class="flex justify-between py-3">
                    <dt class="text-sm text-gray-500">የክፍያ ዘዴ</dt>
                    <dd class="text-sm font-bold text-gray-900">{{ $contribution->method->label() }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm text-gray-500">መጠን</dt>
                    <dd class="text-sm font-bold text-gray-900">ብር {{ number_format($contribution->amount, 2) }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm text-gray-500">ለወር/ጊዜ</dt>
                    <dd class="text-sm font-bold text-gray-900">{{ $contribution->period_covered }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm text-gray-500">የግብይት ቁጥር (ተመላሽ)</dt>
                    <dd class="text-sm font-mono text-gray-900 break-all text-right">{{ $contribution->chapa_tx_ref }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm text-gray-500">ሁኔታ</dt>
                    <dd class="text-sm font-bold">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            በመጠበቅ ላይ
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('member.dashboard') }}" class="flex-1 text-center bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-2xl px-6 py-3.5 transition shadow-sm">
                ወደ ዋና ገጽ ይመለሱ
            </a>
            <a href="{{ route('member.pay') }}" class="flex-1 text-center border border-gray-300 hover:border-gray-400 text-gray-700 font-bold rounded-2xl px-6 py-3.5 transition">
                ሌላ ክፍያ ያድርጉ
            </a>
        </div>

        <p class="text-xs text-gray-400 text-center mt-6 leading-relaxed">
            ክፍያው ከተረጋገጠ በኋላ የእድርዎ ሒሳብ በራስ-ሰር ይዘመናል። ክፍያዎ በስልክዎ ላይ ካልተረጋገጠ፣ እባክዎ ወደ ዋና ገጽ ተመልሰው እንደገና ይሞክሩ።
        </p>
    </div>
@endsection