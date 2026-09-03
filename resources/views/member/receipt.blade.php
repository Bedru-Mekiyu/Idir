<!DOCTYPE html>
<html lang="am" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>የእድር መዋጮ ደረሰኝ - #REC-{{ str_pad($contribution->id, 5, '0', STR_PAD_LEFT) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Ethiopic', sans-serif;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; }
            .receipt-card { box-shadow: none !important; border: 2px solid #000 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center justify-center">

    <!-- Action Toolbar (No print) -->
    <div class="no-print max-w-2xl w-full mb-4 flex items-center justify-between">
        <a href="javascript:history.back()" class="text-sm font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
            &larr; ተመለስ (Back)
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-sm rounded-xl shadow transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>ደረሰኝ አትም (Print Receipt)</span>
        </button>
    </div>

    <!-- Official Receipt Container -->
    <div class="receipt-card max-w-2xl w-full bg-white border-2 border-emerald-800 rounded-2xl shadow-xl p-8 sm:p-10 relative overflow-hidden">
        
        <!-- Decorative Traditional Header Accent -->
        <div class="border-b-2 border-dashed border-emerald-800/40 pb-6 mb-6">
            <div class="flex flex-col sm:flex-row items-center justify-between text-center sm:text-left gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-emerald-800 text-white rounded-xl flex items-center justify-center font-black text-2xl shadow">
                        እ
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-emerald-950 tracking-tight">{{ $idir->name }}</h1>
                        <p class="text-xs text-slate-600 font-semibold">
                            {{ $idir->region ?? 'አዲስ አበባ' }} | {{ $idir->sub_city ?? 'ክፍለ ከተማ' }} | {{ $idir->woreda ?? 'ወረዳ' }}
                        </p>
                    </div>
                </div>

                <div class="text-center sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-200">
                    <span class="inline-block bg-emerald-100 text-emerald-900 font-extrabold text-xs px-3 py-1 rounded-full uppercase tracking-wider">
                        የመዋጮ ደረሰኝ (Receipt)
                    </span>
                    <p class="text-xs font-mono font-bold text-slate-500 mt-1.5">
                        ቁጥር፦ #IDIR-{{ date('Y') }}-{{ str_pad($contribution->id, 5, '0', STR_PAD_LEFT) }}
                    </p>
                    <p class="text-xs text-slate-500 font-medium">ቀን፦ {{ $contribution->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Receipt Body -->
        <div class="space-y-6">
            <!-- Payer & Member Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50/80 p-4 rounded-xl border border-slate-100 text-xs">
                <div>
                    <span class="text-slate-500 font-semibold uppercase block">የከፋይ / የአባል ስም</span>
                    <span class="text-sm font-bold text-slate-900 mt-0.5 block">{{ $member->full_name }}</span>
                    <span class="text-slate-500">{{ $member->phone }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-semibold uppercase block">የተከፈለበት ወር / ጊዜ</span>
                    <span class="text-sm font-bold text-emerald-800 mt-0.5 block">{{ $contribution->period_covered }} ወር</span>
                    <span class="text-slate-500">የክፍያ ዘዴ፦ {{ $contribution->method->label() }}</span>
                </div>
            </div>

            <!-- Financial Table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
                <table class="w-full text-left">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="p-3">ተ.ቁ</th>
                            <th class="p-3">ዝርዝር መግለጫ</th>
                            <th class="p-3">ዓይነት</th>
                            <th class="p-3 text-right">የገንዘብ መጠን</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="p-3 font-mono">01</td>
                            <td class="p-3 font-semibold text-slate-900">
                                የ{{ $contribution->period_covered }} ወርሃዊ መዋጮ ክፍያ
                                @if($contribution->is_correction)
                                    <span class="text-[10px] bg-amber-100 text-amber-800 px-1 py-0.5 rounded font-normal ml-1">ማስተካከያ</span>
                                @endif
                                @if($contribution->in_kind_description)
                                    <p class="text-[11px] text-slate-500 mt-0.5">በዓይነት፦ {{ $contribution->in_kind_description }}</p>
                                @endif
                            </td>
                            <td class="p-3 text-slate-600">{{ $contribution->type->label() }}</td>
                            <td class="p-3 text-right font-bold text-slate-900">
                                {{ number_format($contribution->amount, 2) }} ብር
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-emerald-50/50 border-t-2 border-emerald-800/20 font-extrabold text-slate-900">
                        <tr>
                            <td colspan="3" class="p-3.5 text-right font-black text-sm">ጠቅላላ ድምር (Total)፦</td>
                            <td class="p-3.5 text-right font-black text-base text-emerald-800">
                                {{ number_format($contribution->amount, 2) }} ብር (ETB)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes & Chapa Verification Ref if digital -->
            @if($contribution->chapa_tx_ref)
                <div class="p-3 bg-blue-50/80 border border-blue-100 rounded-xl text-xs text-blue-900 flex items-center justify-between">
                    <div>
                        <span class="font-bold">የዲጂታል ክፍያ ማረጋገጫ (Chapa Tx Ref)፦</span>
                        <span class="font-mono ml-1">{{ $contribution->chapa_tx_ref }}</span>
                    </div>
                    <span class="bg-blue-200 text-blue-900 px-2 py-0.5 rounded text-[10px] font-bold">የተረጋገጠ</span>
                </div>
            @endif

            <!-- Signatures Section -->
            <div class="pt-8 grid grid-cols-2 gap-8 text-center text-xs">
                <div>
                    <div class="border-b border-slate-400 pb-1 mb-1 font-semibold text-slate-800">
                        {{ $contribution->recordedBy->full_name ?? 'የእድሩ ገንዘብ ያዥ' }}
                    </div>
                    <span class="text-slate-500 font-medium">ገንዘብ ተቀባይ / ያዥ ፊርማ</span>
                </div>
                <div>
                    <div class="border-b border-slate-400 pb-1 mb-1 font-semibold text-slate-800">
                        {{ $member->full_name }}
                    </div>
                    <span class="text-slate-500 font-medium">የአባሉ ፊርማ</span>
                </div>
            </div>

            <!-- Watermark / Footer note -->
            <div class="text-center pt-4 border-t border-dashed border-slate-200 text-[11px] text-slate-400">
                ይህ ደረሰኝ በ{{ $idir->name }} ዲጂታል ሥርዓት የተዘጋጀ ሕጋዊ የክፍያ ማረጋገጫ ነው።
            </div>
        </div>
    </div>
</body>
</html>
