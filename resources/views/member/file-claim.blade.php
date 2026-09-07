@extends('layouts.member')

@section('title', 'የክፍያ / የእርዳታ ጥያቄ ማቅረቢያ')

@section('content')
<div class="max-w-2xl mx-auto my-4 sm:my-8">
    <div class="bg-white rounded-3xl shadow-[0_0_30px_rgba(0,0,0,0.5)] shadow-gray-200/50 border border-gray-200 p-4 sm:p-8 sm:p-10 relative overflow-hidden">
        
        <!-- Top Traditional Accent Ribbon -->
        <div class="absolute top-0 left-0 right-0 h-1.5 tibeb-ribbon"></div>

        <!-- Header -->
        <div class="mb-8 pt-2">
            <div class="w-12 h-12 rounded-2xl bg-gray-100 border border-gray-200 border border-gray-200 text-gray-900 flex items-center justify-center font-bold text-xl mb-4 shadow-none">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">የካሳ / የእርዳታ ክፍያ ጥያቄ ማቅረቢያ</h1>
            <p class="text-xs text-gray-500 font-medium mt-1">በእድሩ ደንብ መሰረት የቀብር፣ የሰርግ ወይም የድንገተኛ አደጋ ድጋፍ ጥያቄዎን ለኮሚቴው ያቅርቡ</p>
        </div>

        @if(!$isVested)
            <div class="mb-6 p-4 bg-gray-50 border border-gray-200/80 text-gray-950 rounded-2xl text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-700 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div>
                    <span class="font-bold text-gray-900 block mb-0.5">የብቃት ጊዜ ማስታወሻ፦</span>
                    <p class="font-medium text-gray-800">እስካሁን በእድሩ የብቃት ጊዜ ውስጥ ስለሆኑ፣ ያቀረቡት ጥያቄ በልዩ የኮሚቴ ውሳኔ ብቻ ሊታይ ይችላል።</p>
                </div>
            </div>
        @endif

        <form action="{{ route('member.claims.store', [], false) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Trigger Type -->
            <div>
                <label for="payout_trigger_type_id" class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">
                    የክፍያ ምክንያት <span class="text-black">*</span>
                </label>
                <select name="payout_trigger_type_id" id="payout_trigger_type_id" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm font-medium focus:bg-white focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 transition outline-none">
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
                <label for="requested_amount" class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">
                    የተጠየቀው የገንዘብ መጠን (በብር)
                </label>
                <div class="relative">
                    <input type="number" step="0.01" name="requested_amount" id="requested_amount" value="{{ old('requested_amount') }}"
                        placeholder="ባዶ ቢተዉት በደንቡ የተቀመጠው ነባሪ መጠን ይታሰባል"
                        class="w-full pl-4 pr-16 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm font-numeric font-medium focus:bg-white focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 transition outline-none">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-xs font-bold text-gray-500">
                        ብር
                    </div>
                </div>
                <p class="text-[11px] text-gray-500 font-medium mt-1">አስፈላጊ ካልሆነ ባዶ ይተዉት (በደንቡ መሰረት ይፈጸማል)</p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">
                    ዝርዝር መግለጫ <span class="text-black">*</span>
                </label>
                <textarea name="description" id="description" rows="4" required
                    placeholder="ስለ አጋጣሚው፣ ስለ ቀኑ እና ስለ ሁኔታው ዝርዝር እዚህ ይጻፉ..."
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm font-medium focus:bg-white focus:border-blue-700 focus:ring-2 focus:ring-blue-700/20 transition outline-none">{{ old('description') }}</textarea>
            </div>

            <!-- Document Upload -->
            <div>
                <label for="document" class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">
                    ማስረጃ ሰነድ (የቀበሌ ደብዳቤ፣ የቀብር ምስክር ወረቀት፣ የሰርግ ጥሪ ወዘተ)
                </label>
                <div class="border-2 border-dashed border-gray-200 hover:border-blue-700 rounded-2xl p-6 text-center bg-gray-50 hover:bg-gray-50 transition cursor-pointer">
                    <svg class="w-8 h-8 text-gray-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <input type="file" name="document" id="document" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-100 border border-gray-200 file:text-gray-900 hover:file:bg-blue-100 transition cursor-pointer">
                    <p class="text-[11px] text-gray-500 font-medium mt-2">የሚፈቀዱ አይነቶች፦ PDF, JPG, PNG (እስከ 5MB)</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('member.dashboard') }}" class="px-5 py-3 text-xs font-bold text-gray-600 hover:text-gray-900 transition">
                    ይቅር
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-800 hover:bg-blue-900 text-white font-bold text-sm rounded-xl shadow-md shadow-blue-900/20 transition flex items-center gap-2">
                    <span>ጥያቄውን አቅርብ &rarr;</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

