@extends('layouts.member')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">የፕሮፋይል ቅንብሮች (Profile Settings)</h1>
        <p class="text-gray-500 mt-1">የእርስዎን መረጃ እና ፎቶ ያስተካክሉ</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl p-4 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('member.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-8 flex items-center gap-6">
                <div>
                    <img src="{{ auth()->user()->profile_photo_url }}" class="w-24 h-24 rounded-full object-cover border-4 border-gray-50 shadow-sm bg-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">አዲስ ፎቶ ይምረጡ (Upload Photo)</label>
                    <input type="file" name="profile_photo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                    @error('profile_photo')
                        <p class="text-black text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">ሙሉ ስም (Full Name)</label>
                <input type="text" value="{{ auth()->user()->name }}" disabled class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-500 cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">ስም ለመቀየር እባክዎ የእድርዎን አስተዳዳሪ ያነጋግሩ።</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">ስልክ ቁጥር (Phone Number)</label>
                <input type="text" value="{{ auth()->user()->phone }}" disabled class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-500 cursor-not-allowed">
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition">
                    ቅንብሩን አስቀምጥ (Save Changes)
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
