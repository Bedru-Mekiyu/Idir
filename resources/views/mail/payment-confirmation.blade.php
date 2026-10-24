<x-mail::message>
# ክፍያዎ ተረጋግጧል! ✓

ውድ {{ $member->full_name }}፣

ለ **{{ $idirName }}** ያደረጉት ክፍያ ተረጋግጧል።

| ዝርዝር | መጠን |
|---|---|
| ለወር | {{ $data['period'] ?? '' }} |
| ክፍያ | ብር {{ number_format($data['amount'] ?? 0, 2) }} |

<x-mail::button :url="url('/member')" color="primary">
ወደ ዋና ገጽ ይሂዱ
</x-mail::button>

እናመሰግናለን!<br>
{{ $idirName }}
</x-mail::message>
