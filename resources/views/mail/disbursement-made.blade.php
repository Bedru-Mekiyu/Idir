<x-mail::message>
# ክፍያ ተፈጽሟል! ✓

ውድ {{ $member->full_name }}፣

ከ **{{ $idirName }}** ክፍያ ተፈጽሞልዎታል።

| ዝርዝር | መጠን |
|---|---|
| ምክንያት | {{ $data['trigger'] ?? 'ክፍያ' }} |
| የተከፈለ | ብር {{ number_format($data['amount'] ?? 0, 2) }} |

<x-mail::button :url="url('/member')" color="primary">
ወደ ዋና ገጽ ይሂዱ
</x-mail::button>

እናመሰግናለን!<br>
{{ $idirName }}
</x-mail::message>
