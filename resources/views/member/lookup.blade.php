@extends('layouts.member')

@section('title', 'ፈጣን የአባልነት ፍለጋ')

@section('content')
<div class="max-w-2xl mx-auto my-4 sm:my-8 space-y-8">
    <!-- Search Box -->
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">ፈጣን የአባልነትና የመዋጮ ሁኔታ ፍለጋ</h1>
            <p class="text-xs text-slate-500 mt-1">የተመዘገቡበትን ስልክ ቁጥር በማስገባት የእርስዎን የአባልነትና የመዋጮ ሁኔታ በቀላሉ ይመልከቱ</p>
        </div>

        <form method="POST" action="{{ route('member.lookup.submit') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <input type="tel" name="phone" value="{{ old('phone', request('phone')) }}" required autofocus
                    placeholder="ስልክ ቁጥር ያስገቡ (ለምሳሌ 0911223344)"
                    class="w-full px-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition outline-none">
            </div>
            <button type="submit" class="px-6 py-3.5 bg-brand-700 hover:bg-brand-800 text-white font-bold text-sm rounded-xl shadow-md transition whitespace-nowrap flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>ፈልግ (Lookup)</span>
            </button>
        </form>
    </div>

    <!-- Search Result Card if member is found -->
    @if(isset($member))
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden animate-fade-in">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-brand-700 to-teal-800 p-6 text-white">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-medium">{{ $member->idir->name }}</span>
                            @if($member->fayda_verified)
                                <span class="text-xs bg-emerald-400 text-gray-900 px-2 py-0.5 rounded-full font-bold">ፋይዳ የተረጋገጠ</span>
                            @endif
                        </div>
                        <h2 class="text-2xl font-black mt-2">{{ $member->full_name }}</h2>
                        <p class="text-xs text-brand-100 mt-0.5">{{ $member->phone }} &bull; የተቀላቀለበት፦ {{ $member->join_date }}</p>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                            @if($member->status->value === 'active') bg-emerald-400 text-slate-900
                            @elseif($member->status->value === 'in_arrears') bg-amber-400 text-slate-900
                            @else bg-red-400 text-slate-900 @endif">
                            {{ $member->status->label() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Body Details -->
            <div class="p-6 sm:p-8 space-y-6">
                <!-- Summary Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-xs text-slate-500 font-semibold uppercase">ወርሃዊ መዋጮ</p>
                        <p class="text-lg font-extrabold text-slate-900 mt-1">{{ number_format($member->idir->settings->dues_amount ?? 200, 2) }} ብር</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-xs text-slate-500 font-semibold uppercase">የብቃት ሁኔታ</p>
                        <p class="text-sm font-bold text-slate-900 mt-1">
                            {{ $member->isVested() ? 'ለካሳ ብቁ (Vested)' : 'በብቃት ሂደት ላይ' }}
                        </p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 col-span-2 sm:col-span-1">
                        <p class="text-xs text-slate-500 font-semibold uppercase">የእድር ዓይነት</p>
                        <p class="text-sm font-bold text-slate-900 mt-1 truncate">{{ $member->idir->membership_basis ?? 'ማህበር' }}</p>
                    </div>
                </div>

                <!-- Recent Payments History -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center justify-between">
                        <span>የቅርብ ክፍያዎች (Recent Payments)</span>
                    </h3>

                    @if($member->contributions->isEmpty())
                        <p class="text-xs text-slate-500 text-center py-4 bg-slate-50 rounded-xl">ምንም የተመዘገበ ክፍያ አልተገኘም።</p>
                    @else
                        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                            @foreach($member->contributions as $c)
                                <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/70 transition text-xs">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $c->period_covered }} ወር</p>
                                        <p class="text-slate-400 text-[11px]">{{ $c->created_at->format('M d, Y') }} &bull; {{ $c->method->label() }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-extrabold {{ $c->amount < 0 ? 'text-red-600' : 'text-brand-700' }}">
                                            {{ number_format($c->amount, 2) }} ብር
                                        </span>
                                        <a href="{{ route('member.receipt', $c->id) }}" target="_blank" class="text-brand-700 hover:text-brand-900 underline font-semibold">
                                            ደረሰኝ
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('member.login') }}" class="text-xs text-brand-700 font-bold hover:underline">
                        ወደ ሙሉ የአባላት ገጽ ይግቡ &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
