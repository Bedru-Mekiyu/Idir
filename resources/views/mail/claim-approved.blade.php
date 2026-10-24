<x-mail::message>
# የክፍያ ጥያቄዎ ጸድቋል! ✓

ውድ {{ $member->full_name }}፣

በ **{{ $idirName }}** ያቀረቡት የክፍያ ጥያቄ ጸድቋል።

| ዝርዝር | መጠን |
|---|---|
| ምክንያት | {{ $data['trigger'] ?? 'ክፍያ' }} |
| የተፈቀደ | ብር {{ number_format($data['amount'] ?? 0, 2) }} |

<x-mail::button :url="url('/member')" color="primary">
ወደ ዋና ገጽ ይሂዱ
</x-mail::button>

እናመሰግናለን!<br>
{{ $idirName }}
</x-mail::message>
