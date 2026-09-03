@extends('layouts.member')

@section('title', 'መግቢያ - የአባላት ፖርታል')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-12">
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sm:p-10">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-brand-50 border border-brand-100 text-brand-700 rounded-2xl flex items-center justify-center font-black text-2xl mx-auto shadow-inner mb-4">
                እ
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">የአባላት መግቢያ</h1>
            <p class="text-xs text-slate-500 mt-1">የስልክ ቁጥርዎን እና የይለፍ ቃልዎን በማስገባት ይግቡ</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('member.login.submit') }}" class="space-y-5">
            @csrf

            <!-- Login Input (Phone or Email) -->
            <div>
                <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    ስልክ ቁጥር ወይም ኢሜይል <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                        placeholder="0911223344 ወይም user@example.com"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition outline-none">
                </div>
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        የይለፍ ቃል <span class="text-red-500">*</span>
                    </label>
                </div>
                <input type="password" id="password" name="password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition outline-none">
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-xs text-slate-600">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>አስታውሰኝ (Remember me)</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-700/20 transition transform active:scale-[0.99] flex items-center justify-center gap-2">
                <span>ግባ (Sign In)</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>

        <!-- Quick Status Lookup Callout -->
        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                የይለፍ ቃል የለዎትም? 
                <a href="{{ route('member.lookup') }}" class="text-brand-700 font-bold hover:underline">
                    በስልክ ቁጥር ብቻ ሁኔታዎን ይፈልጉ &rarr;
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
