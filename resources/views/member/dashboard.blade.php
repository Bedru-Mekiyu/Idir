@extends('layouts.member')
@section('content')
    

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">እንኳን ደህና መጡ፣ {{ auth()->user()->name }}</h1>
        <p class="text-gray-500 mt-1">የእድርዎ ቆይታ መረጃዎች ከታች ቀርበዋል</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl p-4 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Profile & Ledger Summary Card -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-xs font-bold text-gray-600 mb-3 border border-gray-200">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                {{ $member->idir->name }}
            </div>
            @if($isVested)
                <div class="mb-3 px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-xs font-bold inline-block">
                    ለክፍያ ብቁ
                </div>
            @else
                <div class="mb-3 px-3 py-1 bg-gray-50 text-black border border-gray-300 rounded-full text-xs font-bold inline-block">
                    በዕዳ የተያዘ
                </div>
            @endif
<h2 class="text-xl font-bold text-gray-900 mb-1">አጠቃላይ መዋጮ</h2>
            <p class="text-gray-500 text-sm">እስካሁን ያዋጡት ጠቅላላ መጠን</p>
        </div>
        <div class="text-left md:text-right flex flex-col items-start md:items-end gap-4">
            <div class="text-3xl sm:text-3xl font-black text-blue-700 tracking-tight bg-blue-50/50 inline-block px-4 py-2 rounded-xl border border-blue-100">
                ብር {{ number_format($contributions->sum('amount'), 2) }}
            </div>
            <a href="{{ route('member.pay') }}" class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl px-5 py-2.5 transition shadow-sm text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                መዋጮ ይክፈሉ
            </a>
        </div>
    </div>

    <!-- Active Claims (If any) -->
    @if($claims->count() > 0)
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-gray-500 animate-pulse"></span>
                በሂደት ላይ ያሉ የካሳ ጥያቄዎች
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($claims as $claim)
                    <div class="bg-white rounded-2xl border border-gray-300 shadow-sm p-5 hover:shadow-md transition relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-gray-100 rounded-bl-[40px] -z-10"></div>
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-bold text-gray-900">{{ $claim->triggerType->name ?? 'ካሳ' }}</h3>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-black border border-gray-300">
                                በመታየት ላይ
                            </span>
                        </div>
                        <p class="text-2xl font-black text-gray-900 tracking-tight mb-3">ብር {{ number_format($claim->requested_amount, 2) }}</p>
                        <p class="text-xs text-gray-500">በ {{ $claim->created_at->format('M d, Y') }} የተጠየቀ</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Contributions -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">የቅርብ ጊዜ መዋጮዎች</h2>
            
        </div>
        
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            @if($contributions->isEmpty())
                <div class="p-4 sm:p-8 text-center text-gray-500">
                    ምንም የተመዘገበ መዋጮ የለም።
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($contributions as $contribution)
                        <li class="p-4 sm:p-5 hover:bg-gray-50 transition flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $contribution->notes ?? 'መደበኛ መዋጮ' }}</p>
                                    <p class="text-xs text-gray-500">{{ $contribution->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-base font-bold text-gray-900">+ ብር {{ number_format($contribution->amount, 2) }}</p>
                                <p class="text-xs font-medium text-blue-700">{{ $contribution->status === 'paid' ? 'ተከፍሏል' : 'በሂደት ላይ' }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection