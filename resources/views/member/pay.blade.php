@extends('layouts.member')
@section('content')

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">የመዋጮ ክፍያ</h1>
        <p class="text-gray-500 mt-1">የሚመርጡትን የክፍያ ዘዴ ይምረጡ</p>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Amount Summary -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8 mb-8 flex flex-col gap-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-xs font-bold text-gray-600 mb-3 border border-gray-200">
                    {{ $member->idir->name }}
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-1">የወር መዋጮ</h2>
                <p class="text-gray-500 text-sm">ለ {{ now()->format('F Y') }} ወር የሚከፈል</p>
            </div>
            <div class="text-left md:text-right">
                <div class="text-3xl sm:text-3xl font-black text-blue-700 tracking-tight bg-blue-50/50 inline-block px-4 py-2 rounded-xl border border-blue-100">
                    ብር {{ number_format($dueAmount, 2) }}
                </div>
            </div>
        </div>

        @if($member->idir->settings?->chapa_subaccount_id)
            <div class="flex items-start gap-2 text-xs text-gray-500 bg-gray-50/80 border border-gray-200 rounded-xl px-3 py-2">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>
                    {{ __('contribution.received_via_subaccount') }}: <span class="font-semibold text-gray-600">{{ $member->idir->settings->chapa_subaccount_id }}</span>
                </span>
            </div>
        @endif
    </div>

    <!-- Payment Method Selection -->
    <form method="POST" action="{{ route('member.pay.submit') }}">
        @csrf

        <h2 class="text-lg font-bold text-gray-900 mb-4">የክፍያ ዘዴ ይምረጡ</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <!-- Telebirr (primary) -->
            <label class="cursor-pointer transition">
                <input type="radio" name="method" value="telebirr" class="peer sr-only" checked>
                <span class="block bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-start gap-4 peer-checked:ring-2 peer-checked:ring-blue-600 peer-checked:border-blue-600">
                    <span class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    </span>
                    <span>
                        <span class="block font-bold text-gray-900">በቴሌብር ይክፈሉ</span>
                        <span class="block text-sm text-gray-500 mt-0.5">Telebirr · በስልክዎ የክፍያ ጥያቄ (USSD) ይደርስዎታል</span>
                    </span>
                </span>
            </label>

            <!-- CBE Birr (primary) -->
            <label class="cursor-pointer transition">
                <input type="radio" name="method" value="cbebirr" class="peer sr-only">
                <span class="block bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-start gap-4 peer-checked:ring-2 peer-checked:ring-red-600 peer-checked:border-red-600">
                    <span class="w-12 h-12 rounded-xl bg-red-50 text-red-700 border border-red-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </span>
                    <span>
                        <span class="block font-bold text-gray-900">በሲቢኢ ብር ይክፈሉ</span>
                        <span class="block text-sm text-gray-500 mt-0.5">CBE Birr · በስልክዎ የክፍያ ጥያቄ (USSD) ይደርስዎታል</span>
                    </span>
                </span>
            </label>

            <!-- Other methods via Chapa hosted checkout (fallback) -->
            <label class="cursor-pointer transition sm:col-span-2">
                <input type="radio" name="method" value="chapa" class="peer sr-only">
                <span class="block bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-start gap-4 peer-checked:ring-2 peer-checked:ring-gray-800 peer-checked:border-gray-800">
                    <span class="w-12 h-12 rounded-xl bg-gray-100 text-gray-700 border border-gray-200 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    </span>
                    <span>
                        <span class="block font-bold text-gray-900">በሌሎች ዘዴዎች ይክፈሉ</span>
                        <span class="block text-sm text-gray-500 mt-0.5">ሌሎች የክፍያ ዘዴዎች (ቻፓ) · ወደ ቻፓ የክፍያ ገጽ ይወሰዳሉ</span>
                    </span>
                </span>
            </label>
        </div>

        <!-- Phone number for USSD direct charge methods -->
        <div id="phone-block" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 mb-6">
            <label for="mobile" class="block text-sm font-bold text-gray-900 mb-1">የስልክ ቁጥር</label>
            <p class="text-xs text-gray-500 mb-3">የክፍያ ማረጋገጫ ጥያቄ (USSD) ወደዚህ ስልክ ቁጥር ይላካል። ካልተገባ ያስተካክሉ።</p>
            <input type="tel" name="mobile" id="mobile" value="{{ $member->phone }}"
                   class="w-full rounded-xl border-gray-300 border px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                   placeholder="09xxxxxxxx" maxlength="20">
        </div>

        <button type="submit"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-2xl px-6 py-4 transition shadow-sm">
            <span id="submit-label">በቴሌብር ይክፈሉ · ብር {{ number_format($dueAmount, 2) }}</span>
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var radios = document.querySelectorAll('input[name="method"]');
            var phoneBlock = document.getElementById('phone-block');
            var submitLabel = document.getElementById('submit-label');
            var labels = {
                telebirr: 'በቴሌብር ይክፈሉ',
                cbebirr: 'በሲቢኢ ብር ይክፈሉ',
                chapa: 'በሌሎች ዘዴዎች ይክፈሉ'
            };

            function sync() {
                var method = document.querySelector('input[name="method"]:checked');
                method = method ? method.value : 'telebirr';
                phoneBlock.classList.toggle('hidden', method === 'chapa');
                submitLabel.textContent = labels[method] + ' · ብር {{ number_format($dueAmount, 2) }}';
            }

            radios.forEach(function (radio) {
                radio.addEventListener('change', sync);
            });

            sync();
        });
    </script>
@endsection