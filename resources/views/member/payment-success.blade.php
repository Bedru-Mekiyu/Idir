@extends('layouts.member')
@section('content')

    <div class="max-w-2xl mx-auto">
        <!-- Success notice -->
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-10 text-center mb-8">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center mb-5">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-2">ክፍያዎ ተረጋግጧል!</h1>
            <p class="text-gray-600 leading-relaxed">
                የክፍያ ሂደትዎ ተጠናቋል። የሒሳብ ሰነድዎ በቅርቡ ይዘመናል።
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('member.dashboard') }}" class="flex-1 text-center bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-2xl px-6 py-3.5 transition shadow-sm">
                ወደ ዋና ገጽ ይመለሱ
            </a>
        </div>

        <p class="text-xs text-gray-400 text-center mt-6 leading-relaxed">
            ክፍያዎ በተሳካ ሁኔታ ተፈጽሟል። እርሶ የከፈሉት መጠን በእድርዎ መዝገብ ላይ በራስ-ሰር ይገባል።
        </p>
    </div>
@endsection
