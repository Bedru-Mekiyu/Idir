@extends('layouts.member')

@section('title', 'ዳሽቦርድ - ' . $member->full_name)

@section('content')
<div class="space-y-8">
    <!-- Member Profile Hero Card -->
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 sm:p-8 relative overflow-hidden">
        <!-- Background decorative ambient gradient -->
        <div class="absolute top-0 right-0 w-80 h-80 bg-gradient-to-bl from-brand-100/50 via-teal-50/30 to-transparent rounded-full pointer-events-none -mr-20 -mt-20"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-white font-black text-2xl sm:text-3xl flex items-center justify-center shadow-lg shadow-brand-700/25">
                    {{ mb_substr($member->full_name, 0, 1) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $member->full_name }}</h1>
                        @if($member->fayda_verified)
                            <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-900 text-xs font-bold px-2.5 py-0.5 rounded-full" title="ፋይዳ ብሔራዊ ዲጂታል መታወቂያ የተረጋገጠ">
                                <svg class="w-3.5 h-3.5 text-emerald-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                ፋይዳ የተረጋገጠ
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-1">
                        {{ $member->phone }} &bull; {{ $member->idir->name }} &bull; የተቀላቀለበት፦ {{ $member->join_date }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('member.claims.create') }}" class="px-5 py-3 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-700/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>የክፍያ ጥያቄ አቅርብ</span>
                </a>
            </div>
        </div>

        <!-- Metrics Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8 pt-6 border-t border-slate-100">
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/80">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">የአባልነት ሁኔታ</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black mt-1
                    @if($member->status->value === 'active') bg-emerald-100 text-emerald-800
                    @elseif($member->status->value === 'in_arrears') bg-amber-100 text-amber-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ $member->status->label() }}
                </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/80">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">የብቃት ሁኔታ (Vesting)</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black mt-1
                    @if($isVested) bg-blue-100 text-blue-800 @else bg-slate-200 text-slate-700 @endif">
                    {{ $isVested ? 'ለክፍያ ብቁ (Vested)' : 'በብቃት ሂደት ላይ' }}
                </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/80">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">ወርሃዊ መዋጮ</span>
                <span class="text-lg font-black text-slate-900 mt-1 block">
                    {{ number_format($member->idir->settings->dues_amount ?? 200, 2) }} ብር
                </span>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/80">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">የተከፈለ ጠቅላላ</span>
                <span class="text-lg font-black text-brand-700 mt-1 block">
                    {{ number_format($contributions->sum('amount'), 2) }} ብር
                </span>
            </div>
        </div>
    </div>

    <!-- Active Claims Status Tracker -->
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 sm:p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">የቀረቡ የክፍያ ጥያቄዎች ሁኔታ (Claim Tracker)</h2>
                <p class="text-xs text-slate-500 mt-0.5">የኮሚቴው ውሳኔ እና የካሳ ክፍያ ሂደት ደረጃዎች</p>
            </div>
            <a href="{{ route('member.claims.create') }}" class="text-xs font-bold text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition">
                + አዲስ ጥያቄ
            </a>
        </div>

        @if($claims->isEmpty())
            <div class="text-center py-10 bg-slate-50/60 rounded-2xl border border-dashed border-slate-200">
                <svg class="w-10 h-10 text-slate-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-semibold text-slate-600">ምንም የቀረበ የክፍያ ጥያቄ የለም።</p>
                <p class="text-xs text-slate-400 mt-1">በአስፈላጊ ወቅት ማስረጃ በማያያዝ ጥያቄዎን ማቅረብ ይችላሉ።</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($claims as $claim)
                    <div class="border border-slate-200 rounded-2xl p-5 bg-slate-50/40 hover:bg-slate-50/80 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-200/80">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-slate-900">{{ $claim->triggerType->label_am ?? 'ጥያቄ' }}</span>
                                    <span class="text-xs bg-slate-200 text-slate-700 px-2 py-0.5 rounded-md font-mono font-semibold">#CLM-{{ $claim->id }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">የቀረበበት ቀን፦ {{ $claim->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="text-sm font-extrabold text-slate-900">
                                    {{ $claim->requested_amount ? number_format($claim->requested_amount, 2) . ' ብር' : 'በደንቡ መሰረት' }}
                                </span>
                            </div>
                        </div>

                        <!-- Step-by-Step Progress Tracker Bar -->
                        <div class="py-5">
                            <div class="grid grid-cols-4 gap-2 text-center text-[11px] font-bold">
                                <!-- Step 1: Submitted -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1 text-white bg-emerald-600 shadow-xs">
                                        ✓
                                    </div>
                                    <span class="text-slate-800">1. ቀረበ</span>
                                </div>

                                <!-- Step 2: Under Review -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1 text-white {{ in_array($claim->status->value, ['under_review', 'approved', 'paid']) ? 'bg-emerald-600' : 'bg-slate-300 text-slate-600' }}">
                                        {{ in_array($claim->status->value, ['under_review', 'approved', 'paid']) ? '✓' : '2' }}
                                    </div>
                                    <span class="{{ in_array($claim->status->value, ['under_review', 'approved', 'paid']) ? 'text-slate-800' : 'text-slate-400' }}">2. በግምገማ ላይ</span>
                                </div>

                                <!-- Step 3: Approved / Rejected -->
                                <div class="flex flex-col items-center">
                                    @if($claim->status->value === 'rejected')
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1 text-white bg-red-600">
                                            ✕
                                        </div>
                                        <span class="text-red-700">ውድቅ ሆነ</span>
                                    @else
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1 text-white {{ in_array($claim->status->value, ['approved', 'paid']) ? 'bg-emerald-600' : 'bg-slate-300 text-slate-600' }}">
                                            {{ in_array($claim->status->value, ['approved', 'paid']) ? '✓' : '3' }}
                                        </div>
                                        <span class="{{ in_array($claim->status->value, ['approved', 'paid']) ? 'text-slate-800' : 'text-slate-400' }}">3. ፀደቀ</span>
                                    @endif
                                </div>

                                <!-- Step 4: Disbursed / Paid -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mb-1 text-white {{ $claim->status->value === 'paid' ? 'bg-emerald-600' : 'bg-slate-300 text-slate-600' }}">
                                        {{ $claim->status->value === 'paid' ? '✓' : '4' }}
                                    </div>
                                    <span class="{{ $claim->status->value === 'paid' ? 'text-emerald-800 font-black' : 'text-slate-400' }}">4. ተከፈለ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Remarks -->
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80 text-xs space-y-1.5">
                            <p class="text-slate-700"><span class="font-bold">ዝርዝር መግለጫ፦</span> {{ $claim->description }}</p>
                            @if($claim->approvals->isNotEmpty())
                                <div class="pt-2 border-t border-slate-100 space-y-1">
                                    <span class="text-slate-500 font-bold block">የኮሚቴ ፈቃዶችና አስተያየቶች፦</span>
                                    @foreach($claim->approvals as $appr)
                                        <div class="text-[11px] text-slate-600 flex items-center justify-between">
                                            <span>&bull; {{ $appr->approver->full_name }} ({{ $appr->decision->label() }})</span>
                                            <span class="text-slate-400">{{ $appr->remarks ?? 'ምንም አስተያየት የለም' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Contributions & Ledger Section -->
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">የመዋጮ ክፍያ ታሪክና ደረሰኞች (Contributions)</h2>
                <p class="text-xs text-slate-500 mt-0.5">የተፈጸሙ ክፍያዎች ዝርዝር እና ኦፊሴላዊ ደረሰኞች</p>
            </div>
        </div>

        @if($contributions->isEmpty())
            <p class="text-sm text-slate-500 text-center py-10">ምንም የተመዘገበ ክፍያ የለም።</p>
        @else
            <div class="divide-y divide-slate-100 border border-slate-100 rounded-2xl overflow-hidden shadow-xs">
                @foreach($contributions as $c)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs
                                @if($c->is_correction) bg-amber-100 text-amber-800
                                @elseif($c->method->value === 'chapa') bg-blue-100 text-blue-800
                                @else bg-emerald-100 text-emerald-800 @endif">
                                {{ $c->method->value === 'chapa' ? 'ዲጂታል' : 'ጥሬ' }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-900">{{ $c->period_covered }} ወር</span>
                                    @if($c->is_correction)
                                        <span class="text-[10px] bg-amber-100 text-amber-900 font-bold px-2 py-0.5 rounded-full">ማስተካከያ</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $c->created_at->format('M d, Y H:i') }} &bull; {{ $c->method->label() }} 
                                    @if($c->notes) &bull; {{ $c->notes }} @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-4 border-t sm:border-t-0 pt-2 sm:pt-0">
                            <span class="text-base font-black {{ $c->amount < 0 ? 'text-red-600' : 'text-brand-700' }}">
                                {{ number_format($c->amount, 2) }} ብር
                            </span>
                            <a href="{{ route('member.receipt', $c->id) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-brand-700 hover:text-brand-900 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                <span>ደረሰኝ አትም</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
