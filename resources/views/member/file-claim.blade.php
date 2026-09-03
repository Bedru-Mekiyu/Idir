@extends('layouts.member')

@section('title', 'የክፍያ / የእርዳታ ጥያቄ ማቅረቢያ')

@section('content')
<div class="max-w-2xl mx-auto my-4 sm:my-8">
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 flex items-center justify-center font-bold text-xl mb-4 shadow-inner">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">የካሳ / የእርዳታ ክፍያ ጥያቄ ማቅረቢያ</h1>
            <p class="text-xs text-slate-500 mt-1">በእድሩ ደንብ መሰረት የቀብር፣ የሰርግ ወይም የድንገተኛ አደጋ ድጋፍ ጥያቄዎን ለኮሚቴው ያቅርቡ</p>
        </div>

        @if(!$isVested)
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div>
                    <span class="font-bold block">የብቃት ጊዜ ማስታወሻ (Vesting Notice)፦</span>
                    እስካሁን በእድሩ የብቃት ጊዜ (Vesting Period) ውስጥ ስለሆኑ፣ ያቀረቡት ጥያቄ በልዩ የኮሚቴ ውሳኔ ብቻ ሊታይ ይችላል።
                </div>
            </div>
        @endif

        <form action="{{ route('member.claims.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Trigger Type -->
            <div>
                <label for="payout_trigger_type_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    የክፍያ ምክንያት (Payout Trigger) <span class="text-red-500">*</span>
                </label>
                <select name="payout_trigger_type_id" id="payout_trigger_type_id" required 
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition outline-none">
                    <option value="">-- የክፍያ ምክንያት ይምረጡ --</option>
                    @foreach($triggerTypes as $type)
                        <option value="{{ $type->id }}" {{ old('payout_trigger_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->label_am }} (ነባሪ ካሳ፦ {{ number_format($type->default_payout_amount, 2) }} ብር)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Requested Amount -->
            <div>
                <label for="requested_amount" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    የተጠየቀው የገንዘብ መጠን (በብር)
                </label>
                <div class="relative">
                    <input type="number" step="0.01" name="requested_amount" id="requested_amount" value="{{ old('requested_amount') }}"
                        placeholder="ባዶ ቢተዉት በደንቡ የተቀመጠው ነባሪ መጠን ይታሰባል"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition outline-none">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">አስፈላጊ ካልሆነ ባዶ ይተዉት (በደንቡ መሰረት ይፈጸማል)</p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    ዝርዝር መግለጫ (Description) <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="4" required
                    placeholder="ስለ አጋጣሚው፣ ስለ ቀኑ እና ስለ ሁኔታው ዝርዝር እዚህ ይጻፉ..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition outline-none">{{ old('description') }}</textarea>
            </div>

            <!-- Document Upload -->
            <div>
                <label for="document" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    ማስረጃ ሰነድ (የቀበሌ ደብዳቤ፣ የቀብር ምስክር ወረቀት፣ የሰርግ ጥሪ ወዘተ)
                </label>
                <div class="border-2 border-dashed border-slate-200 hover:border-brand-500 rounded-2xl p-6 text-center bg-slate-50/50 transition cursor-pointer">
                    <input type="file" name="document" id="document" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="text-[11px] text-slate-400 mt-2">የሚፈቀዱ አይነቶች፦ PDF, JPG, PNG (እስከ 5MB)</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100">
                <a href="{{ route('member.dashboard') }}" class="px-5 py-3 text-xs font-bold text-slate-600 hover:text-slate-900 transition">
                    ይቅር (Cancel)
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-700/20 transition flex items-center gap-2">
                    <span>ጥያቄውን አቅርብ &rarr;</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
