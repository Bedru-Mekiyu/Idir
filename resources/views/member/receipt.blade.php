<!DOCTYPE html>
<html lang="am" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የእድር መዋጮ ደረሰኝ - #REC-{{ str_pad($contribution->id, 5, '0', STR_PAD_LEFT) }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Noto Sans Ethiopic & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Ethiopic', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .font-numeric {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
        }
        .tibeb-ribbon {
            background-color: #1d4ed8;
            background-image: repeating-linear-gradient(
                -45deg,
                #1d4ed8 0px,
                #1d4ed8 16px,
                #ffffff 16px,
                #ffffff 24px,
                #1d4ed8 24px,
                #1d4ed8 40px
            );
        }
        @media print {
            .no-print { display: none !important; }
            body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important; 
            }
            .receipt-card { 
                box-shadow: none !important; 
                border: 2px solid #e7e5e4 !important; 
                margin: 0 auto !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 portrait;
                margin: 12mm;
            }
        }
    </style>
</head>
<body class="overflow-x-hidden leading-loose bg-gray-50 text-gray-900 min-h-screen py-8 px-4 flex flex-col items-center justify-center">

    <!-- Action Toolbar (Hidden during printing) -->
    <div class="no-print max-w-2xl w-full mb-4 flex items-center justify-between">
        <a href="javascript:history.back()" class="text-xs font-bold text-gray-500 hover:text-gray-900 flex items-center gap-1.5 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>ተመለስ</span>
        </a>
        <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>ደረሰኝ አትም</span>
        </button>
    </div>

    <!-- Official Printable Receipt Container -->
    <div class="receipt-card max-w-2xl w-full bg-white border border-gray-200 rounded-3xl shadow-sm p-4 sm:p-8 sm:p-10 relative overflow-hidden">
        
        <!-- Traditional Ethiopian Geometric Ribbon -->
        <div class="absolute top-0 left-0 right-0 h-2 tibeb-ribbon"></div>

        <!-- Decorative Watermark Seal -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03] select-none">
            <div class="w-80 h-80 rounded-full border-8 border-gray-900 flex items-center justify-center font-bold text-8xl text-gray-900">
                እድር
            </div>
        </div>

        <!-- Header -->
        <div class="relative z-10 border-b-2 border-dashed border-gray-200 pb-6 mb-6 pt-2">
            <div class="flex flex-col sm:flex-row items-center justify-between text-center sm:text-left gap-4">
                <div class="flex items-center gap-3.5">
                    <x-logo class="h-10" />
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">{{ $idir->name }}</h1>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">
                            {{ $idir->region ?? 'አዲስ አበባ' }} | {{ $idir->sub_city ?? 'ክፍለ ከተማ' }} | {{ $idir->woreda ?? 'ወረዳ' }}
                        </p>
                    </div>
                </div>

                <div class="text-center sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-gray-200">
                    <span class="inline-block bg-blue-50 border border-blue-100 text-blue-700 font-bold text-xs px-3 py-1 rounded-full uppercase tracking-wider">
                        ኦፊሴላዊ የመዋጮ ደረሰኝ
                    </span>
                    <p class="text-xs font-numeric font-bold text-gray-600 mt-1.5">
                        ቁጥር፦ #IDIR-{{ date('Y') }}-{{ str_pad($contribution->id, 5, '0', STR_PAD_LEFT) }}
                    </p>
                    <p class="text-xs text-gray-500 font-medium">
                        ቀን፦ <span class="font-numeric">{{ $contribution->created_at->format('M d, Y H:i') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Receipt Body -->
        <div class="relative z-10 space-y-6">
            <!-- Payer & Member Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-200 text-xs">
                <div>
                    <span class="text-gray-500 font-bold uppercase tracking-wider block text-[10px]">የከፋይ / የአባል ሙሉ ስም</span>
                    <span class="text-sm font-bold text-gray-900 mt-1 block">{{ $member->full_name }}</span>
                    <span class="text-gray-500 font-numeric font-medium">{{ $member->phone }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-bold uppercase tracking-wider block text-[10px]">የተከፈለበት ወርና የክፍያ ዘዴ</span>
                    <span class="text-sm font-bold text-gray-900 mt-1 block">የ{{ $contribution->period_covered }} ወር መዋጮ</span>
                    <span class="text-gray-500 font-medium">የክፍያ ዘዴ፦ <strong class="text-gray-900">{{ $contribution->method->label() }}</strong></span>
                </div>
            </div>

            <!-- Financial Table -->
            <div class="border border-gray-200 rounded-2xl overflow-hidden text-xs bg-white">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 font-bold border-b border-gray-200">
                        <tr>
                            <th class="p-3 text-center w-12 font-numeric">ተ.ቁ</th>
                            <th class="p-3">ዝርዝር መግለጫ</th>
                            <th class="p-3">ዓይነት</th>
                            <th class="p-3 text-right">የገንዘብ መጠን</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="p-3.5 text-center font-numeric font-bold text-gray-500">01</td>
                            <td class="p-3.5 font-bold text-gray-900">
                                የ{{ $contribution->period_covered }} ወርሃዊ መዋጮ ክፍያ
                                @if($contribution->is_correction)
                                    <span class="text-[10px] bg-gray-100 text-gray-900 border border-gray-200 px-1.5 py-0.5 rounded-md font-bold ml-1.5">ማስተካከያ</span>
                                @endif
                                @if($contribution->in_kind_description)
                                    <p class="text-[11px] text-gray-500 font-medium mt-1">በዓይነት የተሰጠ፦ {{ $contribution->in_kind_description }}</p>
                                @endif
                            </td>
                            <td class="p-3.5 text-gray-600 font-medium">{{ $contribution->type->label() }}</td>
                            <td class="p-3.5 text-right font-bold text-gray-900 font-numeric text-sm">
                                {{ number_format($contribution->amount, 2) }} ብር
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200 font-bold text-gray-900">
                        <tr>
                            <td colspan="3" class="p-4 text-right font-bold text-sm">ጠቅላላ የተከፈለ ድምር፦</td>
                            <td class="p-4 text-right font-bold text-base text-gray-900 font-numeric">
                                {{ number_format($contribution->amount, 2) }} ብር
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Digital Transaction Reference (Telebirr / Chapa) -->
            @if($contribution->chapa_tx_ref)
                <div class="p-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 flex items-center justify-between">
                    <div>
                        <span class="font-bold">የቴሌብር / ቻፓ ዲጂታል ማረጋገጫ ቁጥር፦</span>
                        <span class="font-numeric font-semibold ml-1 text-gray-900"> የክፍያ መለያ: {{ $contribution->chapa_tx_ref }} </span>
                    </div>
                    <span class="bg-white text-blue-700 border border-blue-100 px-2.5 py-0.5 rounded-md text-[10px] font-bold">ዲጂታል የተረጋገጠ</span>
                </div>
            @endif

            <!-- Signatures Section -->
            <div class="pt-8 grid grid-cols-2 gap-10 text-center text-xs">
                <div>
                    <div class="border-b border-gray-300 pb-1.5 mb-1.5 font-bold text-gray-900">
                        {{ $contribution->recordedBy->full_name ?? 'የእድሩ ገንዘብ ያዥ' }}
                    </div>
                    <span class="text-gray-500 font-medium">ገንዘብ ተቀባይ / ያዥ ፊርማ</span>
                </div>
                <div>
                    <div class="border-b border-gray-300 pb-1.5 mb-1.5 font-bold text-gray-900">
                        {{ $member->full_name }}
                    </div>
                    <span class="text-gray-500 font-medium">የከፋዩ አባል ፊርማ</span>
                </div>
            </div>

            <!-- Security Seal & Disclaimer -->
            <div class="text-center pt-6 border-t border-dashed border-gray-200 text-[11px] text-gray-500 font-medium">
                ይህ ደረሰኝ በ{{ $idir->name }} ዲጂታል ሥርዓት የተዘጋጀ ሕጋዊ የክፍያ ማረጋገጫ ሰነድ ነው። (Digital Proof of Contribution)
            </div>
        </div>
    </div>
</body>
</html>
